<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DevelopmentController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('development.modules')[$module] ?? null;
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
        $query = DB::table('dev_'.$module);
        if ($request->filled('q')) {
            $fields = array_keys(array_filter($definition['fields'], fn ($field) => in_array($field['type'], ['text', 'email', 'textarea'], true)));
            $query->where(function ($sub) use ($fields, $request) {
                foreach ($fields as $field) {
                    $sub->orWhere($field, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
                }
                if (! $fields) $sub->whereRaw('1 = 0');
            });
        }
        foreach (array_keys($definition['fields']) as $field) {
            if ($request->filled($field) && in_array($definition['fields'][$field]['type'], ['select', 'relation'], true)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->boolean('overdue') && isset($definition['fields']['due_date'])) {
            $query->whereDate('due_date', '<', today())->whereNotIn('status', ['done', 'completed', 'resolved', 'closed', 'cancelled', 'accepted', 'deployed']);
        }
        return $query;
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'developpement')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre Développement est fermée.');
    }

    private function validateRecord(Request $request, string $module, ?int $record = null): array
    {
        $definition = $this->definition($module);
        $rules = [];
        foreach ($definition['fields'] as $field => $meta) {
            $rule = [];
            $rule[] = $meta['required'] ? 'required' : 'nullable';
            $rule[] = match ($meta['type']) {
                'email' => 'email',
                'number', 'money' => 'numeric',
                'date' => 'date',
                'datetime' => 'date',
                'relation' => 'integer',
                default => 'string',
            };
            if (isset($meta['options'])) $rule[] = Rule::in(array_keys($meta['options']));
            if (in_array($meta['type'], ['text', 'textarea'], true)) $rule[] = 'max:'.($meta['type'] === 'textarea' ? 5000 : 255);
            $rules[$field] = $rule;
        }
        $data = $request->validate($rules);
        if ($module === 'sprints') {
            $project = DB::table('dev_projects')->find($data['project_id'] ?? null);
            if (! $project) throw \Illuminate\Validation\ValidationException::withMessages(['project_id' => 'Projet inexistant.']);
            if ($data['end_date'] < $data['start_date']) throw \Illuminate\Validation\ValidationException::withMessages(['end_date' => 'La fin du sprint ne peut précéder son début.']);
        }
        if ($module === 'tasks' && ! empty($data['sprint_id'])) {
            $sprint = DB::table('dev_sprints')->find($data['sprint_id']);
            if (! $sprint || $sprint->project_id != $data['project_id']) throw \Illuminate\Validation\ValidationException::withMessages(['sprint_id' => 'Le sprint doit appartenir au même projet.']);
        }
        if ($module === 'deliverables' && ! empty($data['task_id'])) {
            $task = DB::table('dev_tasks')->find($data['task_id']);
            if (! $task || $task->project_id != $data['project_id']) throw \Illuminate\Validation\ValidationException::withMessages(['task_id' => 'La tâche doit appartenir au même projet.']);
        }
        if ($module === 'incidents' && ! empty($data['resolved_at']) && $data['resolved_at'] < $data['detected_at']) throw \Illuminate\Validation\ValidationException::withMessages(['resolved_at' => 'La résolution ne peut précéder la détection.']);
        return $data;
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'developpement', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id,
            'action' => $action, 'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'developpement')
            ->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function dashboard()
    {
        $kpis = [
            ['Projets en développement', DB::table('dev_projects')->where('status', 'active')->count(), 'projects', ['status' => 'active']],
            ['Exigences en réalisation', DB::table('dev_requirements')->where('status', 'in_progress')->count(), 'requirements', ['status' => 'in_progress']],
            ['Tâches en cours', DB::table('dev_tasks')->whereIn('status', ['todo', 'active', 'review', 'blocked'])->count(), 'tasks', []],
            ['Tâches en retard', DB::table('dev_tasks')->whereDate('due_date', '<', today())->whereNotIn('status', ['done', 'cancelled'])->count(), 'tasks', ['overdue' => 1]],
            ['Livrables à valider', DB::table('dev_deliverables')->where('status', 'review')->count(), 'deliverables', ['status' => 'review']],
            ['Déploiements échoués', DB::table('dev_deployments')->where('status', 'failed')->count(), 'deployments', ['status' => 'failed']],
            ['Incidents ouverts', DB::table('dev_incidents')->whereIn('status', ['open', 'investigating', 'mitigated'])->count(), 'incidents', []],
            ['Maintenances actives', DB::table('dev_maintenance')->where('status', 'in_progress')->count(), 'maintenance', ['status' => 'in_progress']],
        ];
        return view('back.development.dashboard', [
            'kpis' => $kpis,
            'modules' => config('development.modules'),
            'projects' => DB::table('dev_projects')->orderByDesc('id')->limit(6)->get(),
            'tasks' => DB::table('dev_tasks')->whereNotIn('status', ['done', 'cancelled'])->orderByRaw('due_date is null')->orderBy('due_date')->limit(8)->get(),
            'audits' => $this->auditQuery()->limit(10)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('back.development.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = new \stdClass;
        foreach ($definition['fields'] as $field => $meta) $record->$field = $meta['type'] === 'relation' ? $request->integer($field) ?: null : null;
        return view('back.development.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('dev_'.$module)->find($record);
        abort_unless($record, 404);
        return view('back.development.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('dev_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('development.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'dev_'.$module) $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('dev_'.$key)->where($field, $record->id)->count()];
            }
        }
        return view('back.development.show', compact('module', 'definition', 'record', 'related') + ['lookups' => $this->lookups($definition)]);
    }

    public function store(Request $request, string $module)
    {
        $this->mayWrite($request, $module);
        $id = DB::transaction(function () use ($request, $module) {
            $this->lockOpen();
            $data = $this->validateRecord($request, $module);
            $id = DB::table('dev_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $module, $id, 'create', null, $data);
            return $id;
        });
        return redirect()->route('development.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->mayWrite($request, $module);
        DB::transaction(function () use ($request, $module, $record) {
            $this->lockOpen();
            $before = DB::table('dev_'.$module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $data = $this->validateRecord($request, $module, $record);
            DB::table('dev_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
            $this->audit($request, $module, $record, 'update', $before, $data);
        });
        return redirect()->route('development.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        abort_unless($this->administrative($request), 403);
        try {
            DB::transaction(function () use ($request, $module, $record) {
                $this->lockOpen();
                $before = DB::table('dev_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                DB::table('dev_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Retirez ses dépendances avant de le supprimer.']);
        }
        return redirect()->route('development.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    public function auditLog() { return view('back.development.audit', ['audits' => $this->auditQuery()->paginate(30)]); }

    public function reports()
    {
        $projects = DB::table('dev_projects')->select('dev_projects.*')
            ->selectSub(DB::table('dev_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'dev_projects.id'), 'task_count')
            ->selectSub(DB::table('dev_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'dev_projects.id')->where('status', 'done'), 'task_done')
            ->selectSub(DB::table('dev_incidents')->selectRaw('count(*)')->whereColumn('project_id', 'dev_projects.id')->whereIn('status', ['open', 'investigating', 'mitigated']), 'open_incidents')
            ->selectSub(DB::table('dev_deliverables')->selectRaw('count(*)')->whereColumn('project_id', 'dev_projects.id')->where('status', 'accepted'), 'accepted_deliverables')
            ->orderByDesc('id')->paginate(25);
        return view('back.development.reports', compact('projects'));
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
        }, 'developpement-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
