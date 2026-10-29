<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Models\RhAccessAccount;
use App\Models\User;
use App\Services\RhAuthorization;
use App\Services\RhWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RhController extends Controller
{
    public function __construct(private RhAuthorization $access, private RhWorkflow $workflow) {}

    private function definition(string $module): array
    {
        $definition = config('rh.modules')[$module] ?? null;
        abort_unless($definition, 404);
        return $definition;
    }

    private function scope(Request $request, string $module): string
    {
        $this->definition($module);
        $scope = $this->access->scope($request->user(), $module);
        abort_unless($scope, 403, 'Vous ne disposez pas de cet accès RH.');
        return $scope;
    }

    private function baseQuery(Request $request, string $module)
    {
        $scope = $this->scope($request, $module);
        $query = DB::table('rh_'.$module);
        if ($scope === 'self') {
            $query->where('employee_id', $this->access->employeeId($request->user()));
            if ($module === 'documents') $query->where('visibility', 'employee');
        }
        // Finance receives validated payroll only, never draft salary elements.
        if ($module === 'payroll' && !$this->access->can($request->user(), 'rh.payroll.manage')) $query->whereIn('status', ['approved', 'transmitted']);
        return $query;
    }

    private function query(Request $request, string $module)
    {
        $query = $this->baseQuery($request, $module);
        $fields = $this->definition($module)['fields'];
        if ($request->filled('q')) {
            $text = array_keys(array_filter($fields, fn ($field) => in_array($field['type'], ['text', 'email', 'textarea'])));
            $query->where(function ($sub) use ($text, $request) {
                if (!$text) $sub->whereRaw('1 = 0');
                foreach ($text as $field) $sub->orWhere($field, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
            });
        }
        foreach ($fields as $key => $field) {
            if (in_array($field['type'], ['select', 'relation']) && $request->filled($key)) $query->where($key, $request->input($key));
        }
        if ($request->boolean('expiring') && $module === 'contracts') $query->where('status', 'active')->whereBetween('ends_on', [today(config('rh.timezone'))->toDateString(), today(config('rh.timezone'))->addDays(30)->toDateString()]);
        return $query;
    }

    private function lookups(Request $request, string $module): array
    {
        $lookups = [];
        foreach ($this->definition($module)['fields'] as $key => $field) {
            if ($field['type'] !== 'relation') continue;
            $query = DB::table($field['table']);
            if ($this->scope($request, $module) === 'self') {
                $id = $this->access->employeeId($request->user());
                if ($field['table'] === 'rh_employees') $query->where('id', $id);
                elseif ($field['table'] === 'rh_schedules') $query->where('employee_id', $id);
                else $query->whereRaw('1 = 0');
            }
            if ($module === 'payroll' && !$this->access->can($request->user(), 'rh.payroll.manage') && $field['table'] === 'rh_employees') $query->whereIn('id', $this->baseQuery($request, $module)->select('employee_id'));
            $lookups[$key] = $query->orderBy($field['display'])->pluck($field['display'], 'id')->all();
        }
        return $lookups;
    }

    private function page(Request $request, array $data = []): array
    {
        return $data + ['navigation' => $this->access->visibleModules($request->user()), 'access' => $this->access];
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('back.rh.index', $this->page($request, compact('module', 'definition', 'records') + ['lookups' => $this->lookups($request, $module)]));
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->scope($request, $module);
        abort_unless($this->access->mayWrite($request->user(), $module), 403);
        $record = new \stdClass;
        if ($this->scope($request, $module) === 'self') {
            $record->employee_id = $this->access->employeeId($request->user());
            $record->status = 'pending';
        }
        return view('back.rh.form', $this->page($request, compact('module', 'definition', 'record') + ['lookups' => $this->lookups($request, $module), 'self' => $this->scope($request, $module) === 'self']));
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        abort_unless($this->access->mayWrite($request->user(), $module), 403);
        $record = $this->baseQuery($request, $module)->where('id', $record)->first();
        abort_unless($record, 404);
        return view('back.rh.form', $this->page($request, compact('module', 'definition', 'record') + ['lookups' => $this->lookups($request, $module), 'self' => $this->scope($request, $module) === 'self']));
    }

    public function show(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = $this->baseQuery($request, $module)->where('id', $record)->first();
        abort_unless($record, 404);
        return view('back.rh.show', $this->page($request, compact('module', 'definition', 'record') + ['lookups' => $this->lookups($request, $module)]));
    }

    private function lockAccess(Request $request): void
    {
        // Serializes RH writes, including leave approvals and account revocations.
        $chamber = Chamber::where('slug', 'rh')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403);
        $this->access->refresh();
        abort_unless($request->user()->role === 'superadmin' || $this->access->account($request->user()), 403);
    }

    public function store(Request $request, string $module)
    {
        return $this->save($request, $module, null);
    }

    public function update(Request $request, string $module, int $record)
    {
        return $this->save($request, $module, $record);
    }

    private function save(Request $request, string $module, ?int $record)
    {
        $this->scope($request, $module);
        $uploaded = null;
        try {
            $id = DB::transaction(function () use ($request, $module, $record, &$uploaded) {
                $this->lockAccess($request);
                abort_unless($this->access->mayWrite($request->user(), $module), 403);
                $scope = $this->scope($request, $module);
                $before = $record ? $this->baseQuery($request, $module)->where('id', $record)->lockForUpdate()->first() : null;
                if ($record) abort_unless($before, 404);
                $data = $this->workflow->validate($request, $module, $before, $scope);
                if ($request->hasFile('attachment')) {
                    $uploaded = $request->file('attachment')->store('rh', 'rh_private');
                    $data['attachment'] = $uploaded;
                } else {
                    unset($data['attachment']);
                }
                if ($record) {
                    DB::table('rh_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
                    $id = $record;
                } else {
                    $id = DB::table('rh_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                }
                $this->workflow->audit($request, $module, $id, $record ? 'update' : 'create', $before, $data);
                $this->workflow->afterSave($request, $module, $id, $data);
                return $id;
            });
        } catch (\Throwable $exception) {
            if ($uploaded) Storage::disk('rh_private')->delete($uploaded);
            throw $exception;
        }
        return redirect()->route('rh.show', [$module, $id])->with('success', 'Enregistrement RH sauvegardé.');
    }

    public function download(Request $request, string $module, int $record)
    {
        abort_unless(isset($this->definition($module)['fields']['attachment']), 404);
        $record = $this->baseQuery($request, $module)->where('id', $record)->first();
        abort_unless($record && $record->attachment && Storage::disk('rh_private')->exists($record->attachment), 404);
        $this->workflow->audit($request, $module, $record->id, 'download', null, null);
        return Storage::disk('rh_private')->download($record->attachment, $module.'-'.$record->id.'.'.pathinfo($record->attachment, PATHINFO_EXTENSION));
    }

    public function export(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->scope($request, $module);
        abort_unless($this->access->can($request->user(), 'rh.'.$definition['area'].'.export'), 403);
        $lookups = $this->lookups($request, $module);
        $fields = array_filter($definition['fields'], fn ($field) => $field['type'] !== 'file');
        $this->workflow->audit($request, $module, 0, 'export', null, ['filters' => $request->only(['status', 'employee_id', 'q'])]);
        return response()->streamDownload(function () use ($request, $module, $fields, $lookups) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', ...array_column($fields, 'label'), ...($module === 'balances' ? ['Jours consommés', 'Jours disponibles'] : [])], ';', '"', '');
            foreach ($this->query($request, $module)->orderBy('id')->cursor() as $record) {
                $row = [$record->id];
                foreach ($fields as $key => $field) {
                    $value = (string) ($lookups[$key][$record->$key] ?? $field['options'][$record->$key] ?? $record->$key ?? '');
                    $row[] = preg_match('/^[\s]*[=+@\-]/u', $value) ? "'".$value : $value;
                }
                if ($module === 'balances') array_push($row, $record->used, (float) $record->entitlement - (float) $record->used);
                fputcsv($output, $row, ';', '"', '');
            }
            fclose($output);
        }, 'rh-'.$module.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function dashboard(Request $request)
    {
        $stats = [];
        if ($this->access->can($request->user(), 'rh.personnel.view')) {
            $stats['Employés actifs'] = DB::table('rh_employees')->where('status', 'active')->count();
            $stats['Chambres couvertes'] = DB::table('rh_departments')->whereNotNull('chamber_id')->distinct()->count('chamber_id');
            $stats['Contrats à échéance sous 30 jours'] = $this->query($request->duplicate(['expiring' => '1']), 'contracts')->count();
        }
        if ($this->access->scope($request->user(), 'leaves')) $stats['Congés en attente'] = $this->baseQuery($request, 'leaves')->where('status', 'pending')->count();
        if ($this->access->scope($request->user(), 'attendance')) $stats['Présences du jour'] = $this->baseQuery($request, 'attendance')->whereDate('arrived_at', today(config('rh.timezone')))->count();
        if ($this->access->scope($request->user(), 'payroll')) $stats['Éléments de paie à transmettre'] = $this->baseQuery($request, 'payroll')->where('status', 'approved')->count();
        return view('back.rh.dashboard', $this->page($request, compact('stats')));
    }

    public function reports(Request $request)
    {
        abort_unless($this->access->can($request->user(), 'rh.reports'), 403);
        $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $period = $request->input('period') ?: today(config('rh.timezone'))->format('Y-m');
        $start = \Carbon\CarbonImmutable::createFromFormat('!Y-m', $period, config('rh.timezone'))->startOfMonth();
        $end = $start->endOfMonth();
        $indicators = [];
        if ($this->access->scope($request->user(), 'attendance')) {
            $times = $this->baseQuery($request, 'attendance')->whereBetween('arrived_at', [$start->toDateTimeString(), $end->toDateTimeString()])->selectRaw('coalesce(sum(worked_minutes),0) as worked, coalesce(sum(late_minutes),0) as late, coalesce(sum(overtime_minutes),0) as overtime')->first();
            $indicators['Heures travaillées pointées'] = number_format($times->worked / 60, 2, ',', ' ');
            $indicators['Retards pointés (minutes)'] = $times->late;
            $indicators['Dépassements pointés (minutes, avant validation RH)'] = $times->overtime;
        }
        if ($this->access->scope($request->user(), 'leaves')) $indicators['Jours de congé approuvés débutant ce mois'] = $this->baseQuery($request, 'leaves')->where('status', 'approved')->whereBetween('starts_on', [$start->toDateString(), $end->toDateString()])->sum('days');
        if ($this->access->scope($request->user(), 'payroll')) $indicators['Variables validées ou transmises (FCFA)'] = number_format($this->baseQuery($request, 'payroll')->where('period', $period)->whereIn('status', ['approved', 'transmitted'])->sum('amount'), 0, ',', ' ');
        if ($this->access->scope($request->user(), 'departures')) $indicators['Départs clôturés ce mois'] = $this->baseQuery($request, 'departures')->where('status', 'done')->whereBetween('date', [$start->toDateString(), $end->toDateString()])->count();
        $stats = [];
        foreach ($this->access->visibleModules($request->user()) as $module => $definition) {
            $stats[$module] = ['label' => $definition['label'], 'count' => $this->baseQuery($request, $module)->count()];
        }
        $departments = $this->access->can($request->user(), 'rh.personnel.view')
            ? DB::table('rh_employees')->join('rh_departments', 'rh_departments.id', '=', 'department_id')->select('rh_departments.name')->selectRaw('count(*) as total')->where('rh_employees.status', 'active')->groupBy('rh_departments.id', 'rh_departments.name')->get()
            : collect();
        return view('back.rh.reports', $this->page($request, compact('stats', 'departments', 'indicators', 'period')));
    }

    public function auditLog(Request $request)
    {
        abort_unless($this->access->can($request->user(), 'rh.audit'), 403);
        $audits = DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'user_id')->where('chamber_slug', 'rh')->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id')->paginate(30);
        return view('back.rh.audit', $this->page($request, compact('audits')));
    }

    public function accessIndex(Request $request)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $accounts = RhAccessAccount::with('roles')->get();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('back.rh.access', $this->page($request, compact('accounts', 'users')));
    }

    public function accessStore(Request $request)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id'], 'role' => ['required', Rule::in(['rh-responsable', 'rh-assistant', 'rh-finance', 'rh-employe'])], 'is_active' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $data) {
            $this->lockAccess($request);
            $account = RhAccessAccount::firstOrNew(['user_id' => $data['user_id']]);
            $before = $account->exists ? ['roles' => $account->getRoleNames()->all(), 'is_active' => $account->is_active] : null;
            $account->is_active = $data['is_active'];
            $account->save();
            $account->syncRoles([$data['role']]);
            $this->workflow->audit($request, 'access', $account->id, 'permissions', $before, $data);
        });
        return back()->with('success', 'Les autorisations RH sont mises à jour.');
    }

    public function hire(Request $request, int $record)
    {
        abort_unless($this->access->can($request->user(), 'rh.recruitment.manage') && $this->access->can($request->user(), 'rh.personnel.manage'), 403);
        $input = $request->validate(['registration' => ['required', 'string', 'max:255', 'unique:rh_employees,registration'], 'hired_on' => ['required', 'date_format:Y-m-d']]);
        $id = DB::transaction(function () use ($request, $record, $input) {
            $this->lockAccess($request);
            abort_unless($this->access->can($request->user(), 'rh.recruitment.manage') && $this->access->can($request->user(), 'rh.personnel.manage'), 403);
            validator($input, ['registration' => ['unique:rh_employees,registration']])->validate();
            $candidate = DB::table('rh_applications')->where('id', $record)->lockForUpdate()->first();
            abort_unless($candidate, 404);
            if ($candidate->employee_id || $candidate->status !== 'selected') throw ValidationException::withMessages(['candidate' => 'Seule une candidature sélectionnée et non intégrée peut être recrutée.']);
            $job = DB::table('rh_jobs')->find($candidate->job_id);
            $employee = ['name' => $candidate->name, 'registration' => $input['registration'], 'department_id' => $job->department_id, 'position' => $job->name, 'profession' => 'other', 'email' => $candidate->email, 'phone' => $candidate->phone, 'hired_on' => $input['hired_on'], 'status' => 'onboarding', 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
            $id = DB::table('rh_employees')->insertGetId($employee);
            DB::table('rh_applications')->where('id', $record)->update(['employee_id' => $id, 'status' => 'hired', 'updated_at' => now()]);
            $task = ['employee_id' => $id, 'name' => 'Accueil, pièces et prise de poste', 'status' => 'planned', 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
            $taskId = DB::table('rh_onboarding')->insertGetId($task);
            $this->workflow->audit($request, 'applications', $record, 'hire', $candidate, ['employee_id' => $id, 'status' => 'hired']);
            $this->workflow->audit($request, 'employees', $id, 'create', null, $employee);
            $this->workflow->audit($request, 'onboarding', $taskId, 'create', null, $task);
            return $id;
        });
        return redirect()->route('rh.show', ['employees', $id])->with('success', 'Dossier employé et parcours d’intégration créés.');
    }

    public function transmit(Request $request, int $record)
    {
        abort_unless($this->access->can($request->user(), 'rh.payroll.transfer'), 403);
        DB::transaction(function () use ($request, $record) {
            $this->lockAccess($request);
            abort_unless($this->access->can($request->user(), 'rh.payroll.transfer'), 403);
            $line = $this->baseQuery($request, 'payroll')->where('id', $record)->lockForUpdate()->first();
            abort_unless($line, 404);
            if ($line->status !== 'approved') throw ValidationException::withMessages(['status' => 'Seuls les éléments validés peuvent être marqués transmis.']);
            DB::table('rh_payroll')->where('id', $record)->update(['status' => 'transmitted', 'transmitted_at' => now(), 'updated_at' => now()]);
            $this->workflow->audit($request, 'payroll', $record, 'transmit', $line, ['status' => 'transmitted']);
        });
        return back()->with('success', 'Transmission à Finance enregistrée. Le CSV reste disponible pour le traitement de la paie.');
    }
}
