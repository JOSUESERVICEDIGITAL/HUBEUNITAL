<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JuridiqueController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('juridique.modules')[$module] ?? null;
        abort_unless(is_array($definition), 404);

        return $definition;
    }

    private function administrative(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'superadmin'], true);
    }

    private function mayWrite(Request $request, string $module): void
    {
        abort_if($module === 'team' && ! $this->administrative($request), 403, 'La gestion des accès est réservée aux administrateurs.');
    }

    private function query(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $query = DB::table('jur_'.$module);
        if ($request->filled('q')) {
            $textFields = array_keys(array_filter($definition['fields'], fn ($field) => in_array($field['type'], ['text', 'email', 'textarea'], true)));
            $query->where(function ($sub) use ($textFields, $request) {
                foreach ($textFields as $field) {
                    $sub->orWhere($field, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
                }
                if (! $textFields) {
                    $sub->whereRaw('1 = 0');
                }
            });
        }
        $filters = ['status', ...array_keys(array_filter($definition['fields'], fn ($field) => $field['type'] === 'relation'))];
        foreach ($filters as $field) {
            if (isset($definition['fields'][$field]) && $request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->boolean('overdue') && isset($definition['fields']['due_date'])) {
            $query->whereDate('due_date', '<', today('Africa/Ouagadougou'));
            if (isset($definition['fields']['status'])) {
                $query->whereNotIn('status', ['done', 'cancelled']);
            }
        }

        return $query;
    }

    private function lookups(array $definition): array
    {
        $lookups = [];
        foreach ($definition['fields'] as $field => $meta) {
            if ($meta['type'] === 'relation') {
                $query = DB::table($meta['table']);
                if ($meta['table'] === 'users' && $field !== 'user_id') {
                    $query->where(function ($users) {
                        $users->whereIn('role', ['admin', 'superadmin'])
                            ->orWhereIn('id', DB::table('jur_team')->select('user_id')->where('status', 'active'));
                    });
                }
                $lookups[$field] = $query->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }

        return $lookups;
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('back.juridique.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'juridique', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action,
            'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'juridique')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'superadmin'], true) || DB::table('jur_team')->where('user_id', $user->id)->where('status', 'active')->exists(), 403);
    }

    public function destroy(Request $request, string $module, int $record)
    {
        $this->definition($module);
        abort_unless($this->administrative($request), 403);
        try {
            DB::transaction(function () use ($request, $module, $record) {
                $this->lockOpen();
                $before = DB::table('jur_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                DB::table('jur_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Archivez-le ou retirez ses dépendances avant de le supprimer.']);
            }
            throw $exception;
        }

        return redirect()->route('juridique.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'juridique')
            ->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function auditLog()
    {
        return view('back.juridique.audit', ['audits' => $this->auditQuery()->paginate(30)]);
    }

    public function export(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $lookups = $this->lookups($definition);

        return response()->streamDownload(function () use ($request, $module, $definition, $lookups) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', ...array_column($definition['fields'], 'label')], ';', '"', '');
            foreach ($this->query($request, $module)->orderBy('id')->cursor() as $record) {
                $row = [$record->id];
                foreach ($definition['fields'] as $key => $meta) {
                    $value = $lookups[$key][$record->$key] ?? $meta['options'][$record->$key] ?? $record->$key ?? '';
                    $value = (string) $value;
                    $row[] = preg_match('/^[\s]*[=+@\-]/u', $value) ? "'".$value : $value;
                }
                fputcsv($output, $row, ';', '"', '');
            }
            fclose($output);
        }, 'juridique-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        return view('back.juridique.form', ['module' => $module, 'definition' => $definition, 'record' => new \stdClass, 'lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('jur_'.$module)->find($record);
        abort_unless($record, 404);
        return view('back.juridique.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('jur_'.$module)->find($record);
        abort_unless($record, 404);
        return view('back.juridique.show', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    private function validated(Request $request, string $module, ?int $id): array
    {
        $definition = $this->definition($module);
        $rules = [];
        foreach ($definition['fields'] as $key => $meta) {
            $rules[$key] = [$meta['required'] ? 'required' : 'nullable'];
            $rules[$key] = array_merge($rules[$key], match ($meta['type']) {
                'relation' => ['integer', Rule::exists($meta['table'], 'id')],
                'select' => [Rule::in(array_keys($meta['options']))],
                'date' => ['date_format:Y-m-d'],
                'email' => ['email', 'max:255'],
                'textarea' => ['string', 'max:20000'],
                default => ['string', 'max:255'],
            });
        }
        if ($module === 'team') $rules['user_id'][] = Rule::unique('jur_team', 'user_id')->ignore($id);
        if (isset($definition['fields']['scope'])) {
            $rules['client_id'][] = Rule::requiredIf($request->input('scope') === 'client');
            $rules['partner_id'][] = Rule::requiredIf($request->input('requires_professional') === 'yes');
        }
        $data = $request->validate($rules);
        // Every editable field is replaced, including optional fields cleared by the user.
        foreach ($definition['fields'] as $key => $meta) $data[$key] ??= null;
        if (($data['scope'] ?? null) === 'internal') $data['client_id'] = null;
        return $data;
    }

    public function store(Request $request, string $module)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        $id = DB::transaction(function () use ($request, $module) {
            $this->lockOpen();
            $data = $this->validated($request, $module, null);
            $id = DB::table('jur_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $module, $id, 'create', null, $data);
            return $id;
        });
        return redirect()->route('juridique.show', [$module, $id])->with('success', 'Dossier créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        DB::transaction(function () use ($request, $module, $record) {
            $this->lockOpen();
            $before = DB::table('jur_'.$module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $data = $this->validated($request, $module, $record);
            DB::table('jur_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
            $this->audit($request, $module, $record, 'update', $before, $data);
        });
        return redirect()->route('juridique.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    private function statistics(): array
    {
        $stats = [];
        foreach (config('juridique.modules') as $module => $definition) {
            if (!isset($definition['fields']['due_date'])) continue;
            $active = DB::table('jur_'.$module)->whereNotIn('status', ['done', 'cancelled']);
            $stats[$module] = ['label' => $definition['label'], 'open' => (clone $active)->count(), 'overdue' => (clone $active)->whereDate('due_date', '<', today('Africa/Ouagadougou'))->count(), 'review' => DB::table('jur_'.$module)->where('status', 'review')->count()];
        }
        return $stats;
    }

    public function dashboard()
    {
        return view('back.juridique.dashboard', ['stats' => $this->statistics()]);
    }

    public function reports()
    {
        return view('back.juridique.reports', ['stats' => $this->statistics()]);
    }
}
