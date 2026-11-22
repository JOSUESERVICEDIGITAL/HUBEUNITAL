<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DgController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('dg.modules')[$module] ?? null;
        abort_unless(is_array($definition), 404);
        return $definition;
    }

    private function administrative(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'superadmin'], true);
    }

    private function mayWrite(Request $request, string $module): void
    {
        abort_if($module === 'team' && ! $this->administrative($request), 403, 'La gestion de l’équipe est réservée aux administrateurs.');
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'strategie')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
    }

    private function lookups(array $definition): array
    {
        $lookups = [];
        foreach ($definition['fields'] as $field => $meta) {
            if (($meta['type'] ?? null) === 'relation') {
                $lookups[$field] = DB::table($meta['table'])->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }
        return $lookups;
    }

    private function query(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $query = DB::table('dg_'.$module);
        if ($request->filled('q')) {
            $fields = array_keys(array_filter($definition['fields'], fn ($field) => in_array($field['type'], ['text', 'email', 'textarea', 'url'], true)));
            $query->where(function ($sub) use ($fields, $request) {
                foreach ($fields as $field) $sub->orWhere($field, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
                if (! $fields) $sub->whereRaw('1 = 0');
            });
        }
        foreach ($definition['fields'] as $field => $meta) {
            if (in_array($meta['type'], ['select', 'relation'], true) && $request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->boolean('upcoming') && isset($definition['fields']['starts_at'])) $query->where('starts_at', '>=', now())->where('status', 'planned');
        if ($request->boolean('expiring') && isset($definition['fields']['ends_on'])) $query->whereNotNull('ends_on')->whereBetween('ends_on', [today(), today()->addDays(60)])->where('status', 'active');
        if ($request->boolean('overdue') && isset($definition['fields']['due_on'])) $query->whereDate('due_on', '<', today())->whereNotIn('status', ['completed', 'cancelled']);
        return $query;
    }

    private function validateRecord(Request $request, string $module, ?object $before = null): array
    {
        $definition = $this->definition($module);
        $rules = [];
        foreach ($definition['fields'] as $field => $meta) {
            $required = $meta['required'] ?? false;
            if ($meta['type'] === 'image') $required = $required && ! $before?->{$field};
            $rule = [$required ? 'required' : 'nullable'];
            $rule[] = match ($meta['type']) {
                'email' => 'email',
                'url' => 'url',
                'number' => 'integer',
                'date' => 'date',
                'datetime' => 'date',
                'image' => 'image',
                'relation' => 'integer',
                default => 'string',
            };
            if ($meta['type'] === 'image') array_push($rule, 'mimes:jpg,jpeg,png,webp', 'max:5120');
            if (isset($meta['options'])) $rule[] = Rule::in(array_keys($meta['options']));
            if (in_array($meta['type'], ['text', 'textarea', 'url'], true)) $rule[] = 'max:'.($meta['type'] === 'textarea' ? 5000 : 255);
            if (($meta['type'] ?? null) === 'relation') $rule[] = 'exists:'.$meta['table'].',id';
            $rules[$field] = $rule;
        }
        $data = $request->validate($rules);
        if ($module === 'leadership' && ! empty($data['mandate_ends_on']) && ! empty($data['mandate_starts_on']) && $data['mandate_ends_on'] < $data['mandate_starts_on']) {
            throw ValidationException::withMessages(['mandate_ends_on' => 'La fin du mandat ne peut précéder son début.']);
        }
        if ($module === 'agreements' && ! empty($data['ends_on']) && ! empty($data['starts_on']) && $data['ends_on'] < $data['starts_on']) {
            throw ValidationException::withMessages(['ends_on' => 'La fin de l’accord ne peut précéder son début.']);
        }
        if ($module === 'priorities' && ((int) $data['progress'] < 0 || (int) $data['progress'] > 100)) {
            throw ValidationException::withMessages(['progress' => 'L’avancement doit être compris entre 0 et 100 %.']);
        }
        return $data;
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'strategie',
            'user_id' => $request->user()->id,
            'module' => $module,
            'record_id' => $id,
            'action' => $action,
            'before' => $before ? json_encode($before) : null,
            'after' => $after ? json_encode($after) : null,
            'created_at' => now(),
        ]);
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')
            ->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'strategie')
            ->select('chamber_audits.*', 'users.name as actor')
            ->orderByDesc('chamber_audits.id');
    }

    public function dashboard()
    {
        $kpis = [
            ['Partenaires actifs', DB::table('dg_partners')->where('status', 'active')->count(), 'partners', ['status' => 'active']],
            ['Accords en vigueur', DB::table('dg_agreements')->where('status', 'active')->count(), 'agreements', ['status' => 'active']],
            ['Accords à renouveler', DB::table('dg_agreements')->where('status', 'active')->whereBetween('ends_on', [today(), today()->addDays(60)])->count(), 'agreements', ['expiring' => 1]],
            ['Rendez-vous à venir', DB::table('dg_meetings')->where('status', 'planned')->where('starts_at', '>=', now())->count(), 'meetings', ['upcoming' => 1]],
            ['Priorités en cours', DB::table('dg_priorities')->where('status', 'active')->count(), 'priorities', ['status' => 'active']],
            ['Priorités en retard', DB::table('dg_priorities')->whereDate('due_on', '<', today())->whereNotIn('status', ['completed', 'cancelled'])->count(), 'priorities', ['overdue' => 1]],
            ['Dirigeants enregistrés', DB::table('dg_leadership')->count(), 'leadership', []],
        ];
        return view('back.dg.dashboard', [
            'kpis' => $kpis,
            'modules' => config('dg.modules'),
            'leaders' => DB::table('dg_leadership')->orderByRaw("case when status = 'active' then 0 else 1 end")->orderByDesc('id')->limit(4)->get(),
            'partners' => DB::table('dg_partners')->whereIn('status', ['active', 'contacted'])->orderByDesc('id')->limit(6)->get(),
            'agreements' => DB::table('dg_agreements')->where('status', 'active')->orderBy('ends_on')->limit(6)->get(),
            'audits' => $this->auditQuery()->limit(8)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('back.dg.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = new \stdClass;
        foreach ($definition['fields'] as $field => $meta) $record->$field = $meta['type'] === 'relation' ? $request->integer($field) ?: null : null;
        return view('back.dg.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('dg_'.$module)->find($record);
        abort_unless($record, 404);
        return view('back.dg.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('dg_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('dg.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'dg_'.$module) $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('dg_'.$key)->where($field, $record->id)->count()];
            }
        }
        return view('back.dg.show', compact('module', 'definition', 'record', 'related') + ['lookups' => $this->lookups($definition)]);
    }

    public function store(Request $request, string $module)
    {
        $this->mayWrite($request, $module);
        $uploaded = [];
        try {
            $id = DB::transaction(function () use ($request, $module, &$uploaded) {
                $this->lockOpen();
                $data = $this->validateRecord($request, $module);
                foreach ($this->definition($module)['fields'] as $field => $meta) {
                    if ($meta['type'] !== 'image') continue;
                    if ($request->hasFile($field)) {
                        $path = $request->file($field)->store('dg/'.$module, 'public');
                        if (! $path) throw new \RuntimeException('Impossible d’enregistrer l’image téléversée.');
                        $uploaded[] = $path;
                        $data[$field] = $path;
                    } else {
                        unset($data[$field]);
                    }
                }
                $id = DB::table('dg_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                $this->audit($request, $module, $id, 'create', null, $data);
                return $id;
            });
        } catch (\Throwable $exception) {
            foreach ($uploaded as $path) Storage::disk('public')->delete($path);
            throw $exception;
        }
        return redirect()->route('dg.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->mayWrite($request, $module);
        $uploaded = [];
        $replaced = [];
        try {
            DB::transaction(function () use ($request, $module, $record, &$uploaded, &$replaced) {
                $this->lockOpen();
                $before = DB::table('dg_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                $data = $this->validateRecord($request, $module, $before);
                foreach ($this->definition($module)['fields'] as $field => $meta) {
                    if ($meta['type'] !== 'image') continue;
                    if ($request->hasFile($field)) {
                        $path = $request->file($field)->store('dg/'.$module, 'public');
                        if (! $path) throw new \RuntimeException('Impossible d’enregistrer l’image téléversée.');
                        $uploaded[] = $path;
                        if ($before->$field) $replaced[] = $before->$field;
                        $data[$field] = $path;
                    } else {
                        unset($data[$field]);
                    }
                }
                DB::table('dg_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
                $this->audit($request, $module, $record, 'update', $before, $data);
            });
        } catch (\Throwable $exception) {
            foreach ($uploaded as $path) Storage::disk('public')->delete($path);
            throw $exception;
        }
        foreach ($replaced as $path) Storage::disk('public')->delete($path);
        return redirect()->route('dg.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        abort_unless($this->administrative($request), 403);
        $images = [];
        try {
            DB::transaction(function () use ($request, $module, $record, &$images) {
                $this->lockOpen();
                $before = DB::table('dg_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                foreach ($this->definition($module)['fields'] as $field => $meta) {
                    if ($meta['type'] === 'image' && $before->$field) $images[] = $before->$field;
                }
                DB::table('dg_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            return back()->withErrors(['delete' => 'Cet élément est lié à des dossiers existants. Retirez les liens avant de le supprimer.']);
        }
        foreach ($images as $path) Storage::disk('public')->delete($path);
        return redirect()->route('dg.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    public function auditLog()
    {
        return view('back.dg.audit', ['audits' => $this->auditQuery()->paginate(30)]);
    }

    public function reports()
    {
        $partners = DB::table('dg_partners')->select('dg_partners.*')
            ->selectSub(DB::table('dg_agreements')->selectRaw('count(*)')->whereColumn('partner_id', 'dg_partners.id'), 'agreement_count')
            ->selectSub(DB::table('dg_agreements')->selectRaw('count(*)')->whereColumn('partner_id', 'dg_partners.id')->where('status', 'active'), 'active_agreements')
            ->orderByDesc('id')->paginate(25);
        return view('back.dg.reports', compact('partners'));
    }

    public function export(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $lookups = $this->lookups($definition);
        return response()->streamDownload(function () use ($request, $module, $definition, $lookups) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            $fields = array_filter($definition['fields'], fn ($field) => $field['type'] !== 'image');
            fputcsv($output, ['ID', ...array_column($fields, 'label')], ';', '"', '');
            foreach ($this->query($request, $module)->orderBy('id')->cursor() as $record) {
                $row = [$record->id];
                foreach ($fields as $key => $meta) {
                    $value = (string) ($lookups[$key][$record->$key] ?? $meta['options'][$record->$key] ?? $record->$key ?? '');
                    $row[] = preg_match('/^[\s]*[=+@\-]/u', $value) ? "'".$value : $value;
                }
                fputcsv($output, $row, ';', '"', '');
            }
            fclose($output);
        }, 'dg-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
