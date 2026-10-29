<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Models\CommercialAccessAccount;
use App\Services\CommercialAuthorization;
use App\Services\CommercialWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CommercialController extends Controller
{
    public function __construct(private CommercialAuthorization $access, private CommercialWorkflow $workflow) {}

    private function definition(string $module): array
    {
        $d = config('commercial.modules')[$module] ?? null;
        abort_unless($d, 404);
        return $d;
    }

    private function scope(Request $request, string $module): string
    {
        $this->definition($module);
        $scope = $this->access->scope($request->user(), $module);
        abort_unless($scope, 403, 'Ce module ne fait pas partie de vos autorisations.');
        return $scope;
    }

    private function baseQuery(Request $request, string $module)
    {
        $scope = $this->scope($request, $module);
        $query = DB::table('com_'.$module);
        if ($scope === 'own') {
            $employee = $this->access->employeeId($request->user());
            if (isset($this->definition($module)['fields']['employee_id'])) $query->where('employee_id', $employee);
            elseif ($module === 'clusters') $query->whereIn('id', DB::table('com_problems')->where('employee_id', $employee)->select('cluster_id'));
            else $query->whereIn('problem_id', DB::table('com_problems')->where('employee_id', $employee)->select('id'));
        }
        if ($scope === 'shared') {
            if ($module === 'problems') $query->where('status', '!=', 'reported');
            elseif ($module !== 'clusters') $query->whereIn('problem_id', DB::table('com_problems')->where('status', '!=', 'reported')->select('id'));
        }
        if ($scope === 'review') $query->where('status', '!=', 'draft');
        if ($module === 'commissions' && $this->access->can($request->user(), 'finance') && !$this->access->can($request->user(), 'sales.manage') && !$this->access->can($request->user(), 'hr')) $query->whereIn('status', ['hr_approved', 'finance_approved']);
        return $query;
    }

    private function query(Request $request, string $module)
    {
        $query = $this->baseQuery($request, $module);
        $request->boolean('archived') ? $query->whereNotNull('archived_at') : $query->whereNull('archived_at');
        $fields = $this->definition($module)['fields'];
        if ($request->filled('q')) {
            $keys = array_keys(array_filter($fields, fn ($f) => in_array($f['type'], ['text', 'textarea', 'email'])));
            $query->where(function ($sub) use ($keys, $request) {
                if (!$keys) $sub->whereRaw('1 = 0');
                foreach ($keys as $key) $sub->orWhere($key, 'like', '%'.mb_substr($request->string('q'), 0, 150).'%');
            });
        }
        foreach ($fields as $key => $f) if (in_array($f['type'], ['select', 'relation']) && $request->filled($key)) $query->where($key, $request->input($key));
        if ($module === 'problems' && $request->filled('stage') && isset(config('commercial.stages')[$request->input('stage')])) $query->where('status', $request->input('stage'));
        return $query;
    }

    private function page(Request $request, array $data = []): array
    {
        return $data + ['navigation' => $this->access->visibleModules($request->user()), 'access' => $this->access, 'archivable' => CommercialWorkflow::ARCHIVABLE];
    }

    private function lookups(Request $request, string $module): array
    {
        $lookups = [];
        foreach ($this->definition($module)['fields'] as $key => $field) {
            if ($field['type'] !== 'relation') continue;
            $table = $field['table'];
            if (str_starts_with($table, 'com_') && $this->access->scope($request->user(), substr($table, 4))) $q = $this->baseQuery($request, substr($table, 4))->whereNull('archived_at');
            else $q = DB::table($table);
            if ($table === 'rh_employees') {
                if ($this->scope($request, $module) === 'own') $q->where('id', $this->access->employeeId($request->user()));
                elseif (!$this->access->can($request->user(), 'sales.manage') && !$this->access->can($request->user(), 'field.manage') && !$this->access->mayWrite($request->user(), $module)) $q->whereIn('id', $this->baseQuery($request, $module)->select('employee_id'));
                $q->whereIn('status', ['active', 'onboarding']);
            }
            if (str_starts_with($table, 'com_') && !$this->access->scope($request->user(), substr($table, 4))) {
                // Peer chambers receive only the labels referenced by their shared dossiers.
                if ($module === 'invoices' && $this->access->can($request->user(), 'finance') && $table === 'com_orders') $q->where('status', 'confirmed');
                elseif ($module === 'invoices' && $this->access->can($request->user(), 'finance') && $table === 'com_clients') $q->whereIn('id', DB::table('com_orders')->where('status', 'confirmed')->select('client_id'));
                else $q->whereIn('id', $this->baseQuery($request, $module)->whereNotNull($key)->select($key));
            }
            $lookups[$key] = $q->orderBy($field['display'])->pluck($field['display'], 'id')->all();
        }
        return $lookups;
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('back.commercial.index', $this->page($request, compact('module', 'definition', 'records') + ['lookups' => $this->lookups($request, $module)]));
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->scope($request, $module);
        abort_unless($this->access->mayWrite($request->user(), $module), 403);
        $record = new \stdClass;
        foreach ($definition['fields'] as $key => $field) {
            if ($field['type'] === 'relation' && $request->filled($key)) $record->$key = $request->integer($key) ?: null;
        }
        $this->relations($request, $module, (array) $record);
        foreach (['opportunity_id' => 'opportunities', 'quote_id' => 'quotes', 'order_id' => 'orders', 'client_id' => 'clients'] as $key => $source) {
            if (empty($record->$key) || !$this->access->scope($request->user(), $source)) continue;
            $parent = $this->baseQuery($request, $source)->whereNull('archived_at')->where('id', $record->$key)->first();
            if (!$parent) continue;
            foreach (['employee_id', 'client_id'] as $field) if (isset($definition['fields'][$field]) && !isset($record->$field)) $record->$field = $parent->$field ?? ($source === 'clients' && $field === 'client_id' ? $parent->id : null);
        }
        if ($this->scope($request, $module) === 'own') $record->employee_id = $this->access->employeeId($request->user());
        return view('back.commercial.form', $this->page($request, compact('module', 'definition', 'record') + ['lookups' => $this->lookups($request, $module), 'items' => collect()]));
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->scope($request, $module);
        abort_unless($this->access->mayWrite($request->user(), $module), 403);
        $record = $this->baseQuery($request, $module)->where('id', $record)->first();
        abort_unless($record, 404);
        abort_if($record->archived_at, 403, 'Restaurez le dossier avant de le modifier.');
        return view('back.commercial.form', $this->page($request, compact('module', 'definition', 'record') + ['lookups' => $this->lookups($request, $module), 'items' => $module === 'quotes' ? DB::table('com_quote_items')->where('quote_id', $record->id)->get() : collect()]));
    }

    public function show(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = $this->baseQuery($request, $module)->where('id', $record)->first();
        abort_unless($record, 404);
        $extra = [];
        if ($module === 'problems') {
            $extra['history'] = DB::table('chamber_audits')->where('chamber_slug', 'commerciale')->where('module', 'problems')->where('record_id', $record->id)->where('action', 'stage')->orderBy('id')->get();
            $extra['clusters'] = $this->access->can($request->user(), 'innovation') ? DB::table('com_clusters')->pluck('name', 'id') : collect();
            $extra['nextStage'] = $this->nextStage($record->status);
            $extra['stageAllowed'] = $this->stageAllowed($request, $record->status);
        }
        if ($module === 'invoices') {
            $extra['payments'] = DB::table('com_payments')->where('invoice_id', $record->id)->get();
            $extra['paid'] = $extra['payments']->sum('amount');
        }
        $extra['documents'] = DB::table('com_documents')->where('module', $module)->where('record_id', $record->id)->get(['id', 'name', 'created_at']);
        $extra['canAttach'] = !$record->archived_at && $this->mayAttach($request, $module);
        $extra['items'] = $module === 'quotes' ? DB::table('com_quote_items')->where('quote_id', $record->id)->get() : collect();
        $extra['related'] = $this->related($request, $module, $record);
        $extra['dossierHistory'] = DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')->where('chamber_slug', 'commerciale')->where('module', $module)->where('record_id', $record->id)->select('chamber_audits.action', 'chamber_audits.created_at', 'users.name as actor')->orderByDesc('chamber_audits.id')->limit(30)->get();
        return view('back.commercial.show', $this->page($request, compact('module', 'definition', 'record') + ['lookups' => $this->lookups($request, $module)] + $extra));
    }

    private function lockAccess(Request $request): void
    {
        $chamber = $this->access->chamber($request->user());
        $locked = Chamber::whereIn('slug', ['commerciale', $chamber])->orderBy('id')->lockForUpdate()->get();
        $this->access->refresh();
        abort_unless($request->user()->role === 'superadmin' || $this->access->account($request->user()), 403);
        abort_unless($locked->firstWhere('slug', $chamber)?->is_open, 403);
    }

    private function relations(Request $request, string $module, array $data): void
    {
        foreach ($this->definition($module)['fields'] as $key => $f) {
            if ($f['type'] !== 'relation' || empty($data[$key]) || !str_starts_with($f['table'], 'com_')) continue;
            $target = substr($f['table'], 4);
            if ($this->access->scope($request->user(), $target)) abort_unless($this->baseQuery($request, $target)->whereNull('archived_at')->where('id', $data[$key])->exists(), 403, 'Le dossier associé ne fait pas partie de votre périmètre ou est archivé.');
            else abort_unless(DB::table($f['table'])->where('id', $data[$key])->whereNull('archived_at')->exists(), 403);
        }
    }

    public function store(Request $request, string $module) { return $this->save($request, $module, null); }
    public function update(Request $request, string $module, int $record) { return $this->save($request, $module, $record); }

    private function save(Request $request, string $module, ?int $record)
    {
        $this->scope($request, $module);
        $id = DB::transaction(function () use ($request, $module, $record) {
            $this->lockAccess($request);
            abort_unless($this->access->mayWrite($request->user(), $module), 403);
            $before = $record ? $this->baseQuery($request, $module)->where('id', $record)->lockForUpdate()->first() : null;
            if ($record) abort_unless($before, 404);
            abort_if($before?->archived_at, 403, 'Le dossier est archivé.');
            $items = $module === 'quotes' ? app(\App\Services\CommercialQuoteLines::class)->read($request, $before) : null;
            $data = $this->workflow->validate($request, $module, $before);
            $this->relations($request, $module, $data);
            if ($record) { DB::table('com_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]); $id = $record; }
            else $id = DB::table('com_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->workflow->audit($request, $module, $id, $record ? 'update' : 'create', $before, $data);
            if ($items !== null) {
                $oldItems = DB::table('com_quote_items')->where('quote_id', $id)->get()->all();
                DB::table('com_quote_items')->where('quote_id', $id)->delete();
                foreach ($items as $item) DB::table('com_quote_items')->insert($item + ['quote_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
                $this->workflow->audit($request, 'quotes', $id, 'quote_lines', $oldItems, $items);
            }
            return $id;
        });
        return redirect()->route('commercial.show', [$module, $id])->with('success', 'Dossier enregistré.');
    }

    public function convert(Request $request, int $record)
    {
        $id = DB::transaction(function () use ($request, $record) {
            $this->lockAccess($request);
            abort_unless($this->access->mayWrite($request->user(), 'prospects'), 403);
            $prospect = $this->baseQuery($request, 'prospects')->where('id', $record)->lockForUpdate()->first();
            abort_unless($prospect, 404);
            abort_if($prospect->archived_at, 403);
            if ($prospect->converted_client_id || $prospect->status !== 'qualified') $this->workflow->fail('status', 'Seul un prospect qualifié et non converti peut devenir client.');
            $client = ['name' => $prospect->name, 'employee_id' => $prospect->employee_id, 'email' => $prospect->email, 'phone' => $prospect->phone, 'location' => $prospect->location, 'status' => 'active', 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
            $id = DB::table('com_clients')->insertGetId($client);
            DB::table('com_prospects')->where('id', $record)->update(['status' => 'converted', 'converted_client_id' => $id, 'updated_at' => now()]);
            $this->workflow->audit($request, 'prospects', $record, 'convert', $prospect, ['converted_client_id' => $id]);
            $this->workflow->audit($request, 'clients', $id, 'create', null, $client);
            return $id;
        });
        return redirect()->route('commercial.show', ['clients', $id])->with('success', 'Le client a été créé et relié au prospect.');
    }

    private function nextStage(string $stage): ?string
    {
        $stages = array_keys(config('commercial.stages'));
        return $stages[array_search($stage, $stages, true) + 1] ?? null;
    }

    private function stageAllowed(Request $request, string $stage): bool
    {
        return match ($stage) {
            'reported' => $this->access->can($request->user(), 'field.report') || $this->access->can($request->user(), 'field.manage'),
            'verifying' => $this->access->can($request->user(), 'innovation'),
            'validated', 'study' => $this->access->can($request->user(), 'engineering'),
            'proposed', 'development', 'tested' => $this->access->can($request->user(), 'solution') || $this->access->can($request->user(), 'engineering'),
            default => false,
        };
    }

    public function advance(Request $request, int $record)
    {
        $input = $request->validate(['stage' => ['required', Rule::in(array_keys(config('commercial.stages')))], 'stage_notes' => ['required', 'string', 'max:10000'], 'cluster_id' => ['nullable', 'integer', 'exists:com_clusters,id']]);
        DB::transaction(function () use ($request, $record, $input) {
            $this->lockAccess($request);
            $case = $this->baseQuery($request, 'problems')->where('id', $record)->lockForUpdate()->first();
            abort_unless($case, 404);
            abort_unless($this->stageAllowed($request, $case->status), 403);
            if ($input['stage'] !== $this->nextStage($case->status)) $this->workflow->fail('stage', 'Respectez les étapes de qualification et de réalisation.');
            if ($input['stage'] === 'proposed' && !DB::table('com_proposals')->where('problem_id', $case->id)->exists()) $this->workflow->fail('stage', 'Rédigez d’abord la proposition de projet et son étude de faisabilité.');
            $solution = DB::table('com_solutions')->where('problem_id', $case->id)->first();
            if ($input['stage'] === 'development' && !$solution) $this->workflow->fail('stage', 'Créez le dossier de solution lié au projet.');
            if ($input['stage'] === 'tested' && !$solution?->results) $this->workflow->fail('stage', 'Renseignez les résultats des tests et les retours utilisateurs.');
            if ($input['stage'] === 'deployed' && (!$solution?->deployed_on || $solution->deployed_on > today('Africa/Ouagadougou')->toDateString())) $this->workflow->fail('stage', 'Renseignez une date de déploiement atteinte.');
            $change = ['status' => $input['stage'], 'stage_notes' => $input['stage_notes']];
            if ($case->status === 'reported') $change['transmitted_at'] = now();
            if ($case->status === 'verifying' && isset($input['cluster_id'])) $change['cluster_id'] = $input['cluster_id'];
            DB::table('com_problems')->where('id', $record)->update($change + ['updated_at' => now()]);
            $this->workflow->audit($request, 'problems', $record, 'stage', ['status' => $case->status], $change);
        });
        return back()->with('success', 'L’étape du besoin a été mise à jour.');
    }

    public function review(Request $request, int $record)
    {
        $input = $request->validate(['legal_status' => ['required', Rule::in(['approved', 'changes'])], 'legal_notes' => ['required', 'string', 'max:10000']]);
        DB::transaction(function () use ($request, $record, $input) {
            $this->lockAccess($request);
            abort_unless($this->access->can($request->user(), 'legal'), 403);
            $contract = $this->baseQuery($request, 'contracts')->where('id', $record)->lockForUpdate()->first();
            abort_unless($contract, 404);
            if ($contract->status !== 'review') $this->workflow->fail('status', 'Le contrat doit être soumis à la vérification juridique.');
            DB::table('com_contracts')->where('id', $record)->update($input + ['reviewed_by' => $request->user()->id, 'updated_at' => now()]);
            $this->workflow->audit($request, 'contracts', $record, 'legal_review', $contract, $input);
        });
        return back()->with('success', 'Avis Juridique enregistré.');
    }

    public function approveCommission(Request $request, int $record)
    {
        DB::transaction(function () use ($request, $record) {
            $this->lockAccess($request);
            $line = $this->baseQuery($request, 'commissions')->where('id', $record)->lockForUpdate()->first();
            abort_unless($line, 404);
            if ($line->status === 'proposed') {
                abort_unless($this->access->can($request->user(), 'hr'), 403);
                $employee = DB::table('rh_employees')->find($line->employee_id);
                if ($employee->user_id === $request->user()->id) $this->workflow->fail('status', 'Votre commission doit être validée par une autre personne.');
                $change = ['status' => 'hr_approved', 'hr_approved_by' => $request->user()->id];
            } elseif ($line->status === 'hr_approved') {
                abort_unless($this->access->can($request->user(), 'finance'), 403);
                abort_unless(Chamber::where('slug', 'rh')->lockForUpdate()->firstOrFail()->is_open, 403, 'La chambre RH est fermée.');
                if ($line->hr_approved_by === $request->user()->id) $this->workflow->fail('status', 'La validation Finance doit être faite par une autre personne que la validation RH.');
                $payroll = ['employee_id' => $line->employee_id, 'period' => $line->period, 'name' => $line->name, 'type' => 'bonus', 'amount' => $line->amount, 'status' => 'approved', 'notes' => 'Commission commerciale #'.$line->id, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
                $payrollId = DB::table('rh_payroll')->insertGetId($payroll);
                $change = ['status' => 'finance_approved', 'payroll_id' => $payrollId];
                DB::table('chamber_audits')->insert(['chamber_slug' => 'rh', 'user_id' => $request->user()->id, 'module' => 'payroll', 'record_id' => $payrollId, 'action' => 'create', 'after' => json_encode($payroll), 'created_at' => now()]);
            } else $this->workflow->fail('status', 'La commission est déjà validée et transmise à la paie.');
            DB::table('com_commissions')->where('id', $record)->update($change + ['updated_at' => now()]);
            $this->workflow->audit($request, 'commissions', $record, 'approval', $line, $change);
        });
        return back()->with('success', 'Validation enregistrée. Après les deux validations, la commission rejoint les éléments de paie RH une seule fois.');
    }

    public function payment(Request $request, int $record)
    {
        $input = $request->validate(['reference' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999'], 'paid_on' => ['required', 'date_format:Y-m-d']]);
        DB::transaction(function () use ($request, $record, $input) {
            $this->lockAccess($request);
            abort_unless($this->access->can($request->user(), 'finance'), 403);
            validator($input, ['reference' => ['unique:com_payments']])->validate();
            $invoice = $this->baseQuery($request, 'invoices')->where('id', $record)->lockForUpdate()->first();
            abort_unless($invoice, 404);
            if ($invoice->status !== 'issued' || (float) DB::table('com_payments')->where('invoice_id', $record)->sum('amount') + (float) $input['amount'] > (float) $invoice->amount) $this->workflow->fail('amount', 'Le paiement dépasse le solde ou la facture n’est pas émise.');
            if ($input['paid_on'] < $invoice->issued_on || $input['paid_on'] > today('Africa/Ouagadougou')->toDateString()) $this->workflow->fail('paid_on', 'La date doit être comprise entre l’émission et aujourd’hui.');
            $data = $input + ['invoice_id' => $record, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
            $id = DB::table('com_payments')->insertGetId($data);
            $this->workflow->audit($request, 'payments', $id, 'create', null, $data);
        });
        return back()->with('success', 'Encaissement enregistré par Finance.');
    }

    public function dashboard(Request $request)
    {
        $stats = [];
        foreach (['visits' => 'Visites réalisées', 'orders' => 'Ventes confirmées', 'contracts' => 'Contrats signés', 'problems' => 'Besoins signalés'] as $module => $label) {
            if (!$this->access->scope($request->user(), $module)) continue;
            $q = $this->baseQuery($request, $module)->whereNull('archived_at');
            if ($module !== 'problems') $q->where('status', ['visits' => 'done', 'orders' => 'confirmed', 'contracts' => 'signed'][$module]);
            $stats[$label] = $q->count();
        }
        if ($this->access->scope($request->user(), 'orders')) $stats['Ventes confirmées (FCFA)'] = number_format($this->baseQuery($request, 'orders')->whereNull('archived_at')->where('status', 'confirmed')->sum('amount'), 0, ',', ' ');
        if ($this->access->scope($request->user(), 'followups')) $stats['Relances en retard'] = $this->baseQuery($request, 'followups')->whereNull('archived_at')->whereNotIn('status', ['done', 'cancelled'])->whereDate('due_date', '<', today('Africa/Ouagadougou'))->count();
        if ($this->access->scope($request->user(), 'invoices')) {
            $invoices = $this->baseQuery($request, 'invoices')->whereNull('archived_at')->where('status', 'issued');
            $billed = (float) (clone $invoices)->sum('amount');
            $received = (float) DB::table('com_payments')->whereIn('invoice_id', (clone $invoices)->select('id'))->sum('amount');
            $stats['Encaissé (FCFA)'] = number_format($received, 0, ',', ' ');
            $stats['Reste à encaisser (FCFA)'] = number_format(max(0, $billed - $received), 0, ',', ' ');
        }
        $nextActions = [];
        foreach (['followups' => 'due_date', 'appointments' => 'starts_at', 'visits' => 'date'] as $module => $dateField) {
            if ($this->access->scope($request->user(), $module)) $nextActions[$module] = $this->baseQuery($request, $module)->whereNull('archived_at')->whereNotIn('status', ['done', 'cancelled'])->orderBy($dateField)->limit(5)->get();
        }
        return view('back.commercial.dashboard', $this->page($request, compact('stats', 'nextActions')));
    }

    public function statistics(Request $request)
    {
        $this->scope($request, 'targets');
        $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $period = $request->input('period') ?: today('Africa/Ouagadougou')->format('Y-m');
        $targets = $this->baseQuery($request, 'targets')->whereNull('archived_at')->where('period', $period)->get();
        $sales = $this->baseQuery($request, 'orders')->where('status', 'confirmed')->where('date', 'like', $period.'%')->select('employee_id')->selectRaw('sum(amount) as total')->groupBy('employee_id')->pluck('total', 'employee_id');
        $visits = $this->baseQuery($request, 'visits')->whereNull('archived_at')->where('status', 'done')->where('date', 'like', $period.'%')->select('employee_id')->selectRaw('count(*) as total')->groupBy('employee_id')->pluck('total', 'employee_id');
        foreach ($targets as $target) {
            $target->achieved = $sales[$target->employee_id] ?? 0;
            $target->visits = $visits[$target->employee_id] ?? 0;
        }
        return view('back.commercial.statistics', $this->page($request, compact('targets', 'period') + ['lookups' => $this->lookups($request, 'targets')]));
    }

    private function teamEmployees(Request $request)
    {
        $query = DB::table('rh_employees')->whereIn('status', ['active', 'onboarding']);
        if ($this->access->can($request->user(), 'sales.manage')) {
            $query->whereIn('id', CommercialAccessAccount::where('is_active', true)->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['commercial-agent', 'commercial-veille']))->select('employee_id'));
        } else {
            abort_unless($this->access->can($request->user(), 'sales.agent') || $this->access->can($request->user(), 'field.report'), 403);
            $query->where('id', $this->access->employeeId($request->user()));
        }
        return $query;
    }

    public function availability(Request $request)
    {
        abort_unless(Chamber::where('slug', 'rh')->firstOrFail()->is_open, 403, 'La chambre RH est fermée.');
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $date = $request->input('date') ?: today('Africa/Ouagadougou')->toDateString();
        $employees = $this->teamEmployees($request)->get(['id', 'name', 'registration']);
        $employeeIds = $employees->pluck('id');
        $leaves = DB::table('rh_leaves')->whereIn('employee_id', $employeeIds)->where('status', 'pending')->get(['id', 'employee_id', 'starts_on', 'ends_on', 'days', 'commercial_opinion', 'commercial_notes']);
        $away = DB::table('rh_leaves')->whereIn('employee_id', $employeeIds)->where('status', 'approved')->where('starts_on', '<=', $date)->where('ends_on', '>=', $date)->pluck('employee_id')->all();
        $absent = DB::table('rh_absences')->whereIn('employee_id', $employeeIds)->where('starts_on', '<=', $date)->where('ends_on', '>=', $date)->pluck('employee_id')->all();
        $present = DB::table('rh_attendance')->whereIn('employee_id', $employeeIds)->whereDate('arrived_at', $date)->pluck('employee_id')->all();
        return view('back.commercial.availability', $this->page($request, compact('date', 'employees', 'leaves', 'away', 'absent', 'present')));
    }

    public function leaveOpinion(Request $request, int $record)
    {
        $input = $request->validate(['commercial_opinion' => ['required', Rule::in(['favorable', 'unfavorable'])], 'commercial_notes' => ['required', 'string', 'max:5000']]);
        DB::transaction(function () use ($request, $record, $input) {
            $this->lockAccess($request);
            abort_unless($this->access->can($request->user(), 'sales.manage'), 403);
            // Use the RH chamber lock as well to serialize with RH leave edits/decisions.
            $rh = Chamber::where('slug', 'rh')->lockForUpdate()->firstOrFail();
            abort_unless($rh->is_open, 403, 'La chambre RH est fermée.');
            $leave = DB::table('rh_leaves')->where('id', $record)->whereIn('employee_id', $this->teamEmployees($request)->select('id'))->lockForUpdate()->first();
            abort_unless($leave, 404);
            if ($leave->status !== 'pending') $this->workflow->fail('status', 'La demande a déjà reçu une décision RH.');
            if (DB::table('rh_employees')->where('id', $leave->employee_id)->value('user_id') === $request->user()->id) $this->workflow->fail('commercial_opinion', 'Un autre responsable doit donner l’avis sur votre demande.');
            DB::table('rh_leaves')->where('id', $record)->update($input + ['commercial_reviewed_by' => $request->user()->id, 'updated_at' => now()]);
            DB::table('chamber_audits')->insert(['chamber_slug' => 'rh', 'user_id' => $request->user()->id, 'module' => 'leaves', 'record_id' => $record, 'action' => 'commercial_opinion', 'before' => json_encode(['commercial_opinion' => $leave->commercial_opinion]), 'after' => json_encode($input), 'created_at' => now()]);
        });
        return back()->with('success', 'Avis commercial transmis à la RH. La décision finale et le solde restent gérés par la RH.');
    }

    public function map(Request $request)
    {
        $cases = $this->query($request, 'problems')->get(['id', 'name', 'location', 'sector', 'latitude', 'longitude', 'status']);
        $locations = $cases->groupBy('location');
        return view('back.commercial.map', $this->page($request, compact('cases', 'locations')));
    }

    public function handoffs(Request $request)
    {
        $cases = $this->query($request, 'problems')->where('status', '!=', 'reported')->orderByDesc('transmitted_at')->paginate(20)->withQueryString();
        return view('back.commercial.handoffs', $this->page($request, compact('cases')));
    }

    public function export(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->scope($request, $module);
        abort_unless($this->access->can($request->user(), 'export') || ($module === 'invoices' && $this->access->can($request->user(), 'finance')), 403);
        $lookups = $this->lookups($request, $module);
        $this->workflow->audit($request, $module, 0, 'export', null, null);
        return response()->streamDownload(function () use ($request, $module, $definition, $lookups) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            $extras = match ($module) { 'problems' => ['status'], 'orders' => ['amount'], 'commissions' => ['amount', 'status'], default => [] };
            fputcsv($out, ['ID', ...array_column($definition['fields'], 'label'), ...$extras], ';', '"', '');
            foreach ($this->query($request, $module)->orderBy('id')->cursor() as $record) {
                $row = [$record->id];
                foreach ($definition['fields'] as $key => $f) $row[] = (string) ($lookups[$key][$record->$key] ?? $f['options'][$record->$key] ?? $record->$key ?? '');
                foreach ($extras as $key) $row[] = $record->$key;
                $row = array_map(fn ($value) => preg_match('/^[\s]*[=+@\-]/u', (string) $value) ? "'".$value : $value, $row);
                fputcsv($out, $row, ';', '"', '');
            }
            fclose($out);
        }, 'commercial-'.$module.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function auditLog(Request $request)
    {
        abort_unless($this->access->can($request->user(), 'audit'), 403);
        $audits = DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')->where('chamber_slug', 'commerciale')->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id')->paginate(30);
        return view('back.commercial.audit', $this->page($request, compact('audits')));
    }

    private function related(Request $request, string $module, object $record): array
    {
        $related = [];
        foreach ($this->access->visibleModules($request->user()) as $target => $definition) {
            foreach ($definition['fields'] as $field => $meta) {
                if (($meta['table'] ?? null) !== 'com_'.$module) continue;
                $related[] = ['module' => $target, 'field' => $field, 'label' => $definition['label'], 'count' => $this->baseQuery($request, $target)->whereNull('archived_at')->where($field, $record->id)->count()];
            }
        }
        return $related;
    }

    public function pipeline(Request $request)
    {
        $records = $this->query($request, 'opportunities')->orderBy('due_date')->get();
        $stages = config('commercial.modules.opportunities.fields.status.options');
        return view('back.commercial.pipeline', $this->page($request, compact('records', 'stages')));
    }

    public function moveOpportunity(Request $request, int $record)
    {
        $input = $request->validate(['status' => ['required', Rule::in(array_keys(config('commercial.modules.opportunities.fields.status.options')))]]);
        DB::transaction(function () use ($request, $record, $input) {
            $this->lockAccess($request);
            abort_unless($this->access->mayWrite($request->user(), 'opportunities'), 403);
            $before = $this->baseQuery($request, 'opportunities')->whereNull('archived_at')->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            DB::table('com_opportunities')->where('id', $record)->update($input + ['updated_at' => now()]);
            $this->workflow->audit($request, 'opportunities', $record, 'pipeline', ['status' => $before->status], $input);
        });
        return back()->with('success', 'Étape commerciale mise à jour.');
    }

    public function agenda(Request $request)
    {
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d']]);
        $from = \Carbon\CarbonImmutable::parse($request->input('from') ?: today('Africa/Ouagadougou')->toDateString(), 'Africa/Ouagadougou')->startOfDay();
        $until = $from->addDays(6)->endOfDay();
        $events = collect(); $allowed = false;
        foreach (['appointments' => 'starts_at', 'visits' => 'date', 'followups' => 'due_date'] as $module => $field) {
            if (!$this->access->scope($request->user(), $module)) continue;
            $allowed = true;
            $rows = $this->baseQuery($request, $module)->whereNull('archived_at')->whereNotIn('status', ['done', 'cancelled'])->whereBetween($field, [$from->toDateString(), $field === 'starts_at' ? $until->toDateTimeString() : $until->toDateString()])->get();
            foreach ($rows as $row) $events->push(['id' => $row->id, 'module' => $module, 'name' => $row->name, 'date' => $row->$field, 'label' => config('commercial.modules.'.$module.'.label'), 'status' => config('commercial.modules.'.$module.'.fields.status.options.'.$row->status)]);
        }
        abort_unless($allowed, 403);
        return view('back.commercial.agenda', $this->page($request, ['events' => $events->sortBy('date'), 'from' => $from, 'until' => $until]));
    }

    public function archive(Request $request, string $module, int $record)
    {
        $this->scope($request, $module);
        abort_unless(in_array($module, CommercialWorkflow::ARCHIVABLE, true), 403, 'Ce dossier suit son propre circuit de clôture.');
        $input = $request->validate(['archived' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $module, $record, $input) {
            $this->lockAccess($request);
            abort_unless($this->access->mayWrite($request->user(), $module) && ($this->access->can($request->user(), 'sales.manage') || $this->access->can($request->user(), 'field.manage')), 403);
            $before = $this->baseQuery($request, $module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $change = ['archived_at' => $input['archived'] ? now() : null];
            DB::table('com_'.$module)->where('id', $record)->update($change + ['updated_at' => now()]);
            $this->workflow->audit($request, $module, $record, $input['archived'] ? 'archive' : 'restore', ['archived_at' => $before->archived_at], $change);
        });
        return back()->with('success', $input['archived'] ? 'Dossier archivé. Ses données sont conservées.' : 'Dossier restauré.');
    }

    private function mayAttach(Request $request, string $module): bool
    {
        return $this->access->mayWrite($request->user(), $module)
            || ($module === 'contracts' && $this->access->can($request->user(), 'legal'))
            || ($module === 'problems' && ($this->access->can($request->user(), 'innovation') || $this->access->can($request->user(), 'engineering')));
    }

    public function uploadDocument(Request $request, string $module, int $record)
    {
        $input = $request->validate(['name' => ['required', 'string', 'max:255'], 'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']]);
        $path = null;
        try {
            DB::transaction(function () use ($request, $module, $record, $input, &$path) {
                $this->lockAccess($request);
                $parent = $this->baseQuery($request, $module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($parent, 404);
                abort_unless(!$parent->archived_at && $this->mayAttach($request, $module), 403);
                $path = $request->file('document')->store('documents', 'commercial_private');
                $id = DB::table('com_documents')->insertGetId(['module' => $module, 'record_id' => $record, 'name' => $input['name'], 'path' => $path, 'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
                $this->workflow->audit($request, $module, $record, 'document_upload', null, ['document_id' => $id, 'name' => $input['name']]);
            });
        } catch (\Throwable $exception) {
            if ($path) Storage::disk('commercial_private')->delete($path);
            throw $exception;
        }
        return back()->with('success', 'Pièce jointe enregistrée dans le dossier privé.');
    }

    public function downloadDocument(Request $request, string $module, int $record, int $document)
    {
        abort_unless($this->baseQuery($request, $module)->where('id', $record)->exists(), 404);
        $file = DB::table('com_documents')->where('id', $document)->where('module', $module)->where('record_id', $record)->first();
        abort_unless($file && Storage::disk('commercial_private')->exists($file->path), 404);
        $this->workflow->audit($request, $module, $record, 'document_download', null, ['document_id' => $file->id]);
        return Storage::disk('commercial_private')->download($file->path, 'document-'.$file->id.'.'.pathinfo($file->path, PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function printDocument(Request $request, string $module, int $record)
    {
        abort_unless(in_array($module, ['quotes', 'orders', 'invoices', 'contracts']), 404);
        $record = $this->baseQuery($request, $module)->where('id', $record)->first();
        abort_unless($record, 404);
        $client = DB::table('com_clients')->find($record->client_id);
        $quoteId = $module === 'quotes' ? $record->id : ($record->quote_id ?? null);
        if (in_array($module, ['contracts', 'invoices'])) $quoteId = DB::table('com_orders')->where('id', $record->order_id)->value('quote_id');
        $items = $quoteId ? DB::table('com_quote_items')->where('quote_id', $quoteId)->get() : collect();
        // An invoice may cover only part of an order: do not print full-order lines as invoice lines.
        if ($module === 'invoices') $items = collect();
        $this->workflow->audit($request, $module, $record->id, 'print', null, null);
        return view('back.commercial.print', ['module' => $module, 'record' => $record, 'client' => $client, 'items' => $items, 'definition' => $this->definition($module)]);
    }

    public function accessIndex(Request $request)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $accounts = CommercialAccessAccount::with('roles')->get();
        $employees = DB::table('rh_employees')->whereNotNull('user_id')->whereIn('status', ['active', 'onboarding'])->get(['id', 'name', 'registration']);
        return view('back.commercial.access', $this->page($request, compact('accounts', 'employees')));
    }

    public function accessStore(Request $request)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        $input = $request->validate(['employee_id' => ['required', 'integer', 'exists:rh_employees,id'], 'profile' => ['required', Rule::in(['commercial-responsable', 'commercial-agent', 'commercial-veille', 'commercial-innovation', 'commercial-ingenierie', 'commercial-technique', 'commercial-juridique', 'commercial-finance', 'commercial-rh'])], 'is_active' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $input) {
            $this->lockAccess($request);
            $employee = DB::table('rh_employees')->where('id', $input['employee_id'])->lockForUpdate()->first();
            if (!$employee->user_id || !in_array($employee->status, ['active', 'onboarding'])) $this->workflow->fail('employee_id', 'Reliez d’abord ce collaborateur actif à un compte utilisateur dans la RH.');
            $account = CommercialAccessAccount::firstOrNew(['employee_id' => $employee->id]);
            if (CommercialAccessAccount::where('user_id', $employee->user_id)->where('employee_id', '!=', $employee->id)->exists()) $this->workflow->fail('employee_id', 'Ce compte utilisateur dispose déjà d’un autre rattachement commercial.');
            $before = $account->exists ? ['roles' => $account->getRoleNames()->all(), 'is_active' => $account->is_active] : null;
            $account->fill(['user_id' => $employee->user_id, 'is_active' => $input['is_active']])->save();
            $account->syncRoles([$input['profile']]);
            $this->workflow->audit($request, 'access', $account->id, 'permissions', $before, $input);
        });
        return back()->with('success', 'Accès commercial et coordination mis à jour.');
    }
}
