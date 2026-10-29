<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EngineeringController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('engineering.modules')[$module] ?? null;
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
        $query = DB::table('eng_'.$module);
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
            $query->whereDate('due_date', '<', today());
            if (isset($definition['fields']['status'])) {
                $query->whereNotIn('status', ['done', 'completed', 'cancelled', 'accepted', 'lost', 'won', 'expired', 'rejected']);
            }
        }
        if ($module === 'invoices' && $request->boolean('unpaid')) {
            $query->where('status', 'issued')->whereRaw('amount > (select coalesce(sum(amount), 0) from eng_payments where invoice_id = eng_invoices.id)');
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
                            ->orWhereIn('id', DB::table('eng_team')->select('user_id')->where('status', 'active'));
                    });
                }
                $lookups[$field] = $query->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }

        return $lookups;
    }

    public function dashboard()
    {
        $collected = DB::table('eng_payments')->sum('amount');
        $billed = DB::table('eng_invoices')->where('status', 'issued')->sum('amount');
        $kpis = [
            ['Clients actifs', DB::table('eng_clients')->where('status', 'active')->count(), 'clients', ['status' => 'active']],
            ['Opportunités en négociation', DB::table('eng_opportunities')->where('status', 'negotiation')->count(), 'opportunities', ['status' => 'negotiation']],
            ['Projets en cours', DB::table('eng_projects')->where('status', 'active')->count(), 'projects', ['status' => 'active']],
            ['Tâches en retard', DB::table('eng_tasks')->whereDate('due_date', '<', today())->whereNotIn('status', ['done'])->count(), 'tasks', ['overdue' => 1]],
            ['Livrables à valider', DB::table('eng_deliverables')->where('status', 'review')->count(), 'deliverables', ['status' => 'review']],
            ['Devis envoyés', DB::table('eng_quotes')->where('status', 'sent')->count(), 'quotes', ['status' => 'sent']],
            ['Encaissements (FCFA)', number_format((float) $collected, 0, ',', ' '), 'payments', []],
            ['Reste à encaisser (FCFA)', number_format(max(0, (float) $billed - (float) $collected), 0, ',', ' '), 'invoices', ['unpaid' => 1]],
        ];

        return view('back.engineering.dashboard', [
            'kpis' => $kpis,
            'modules' => config('engineering.modules'),
            'projects' => DB::table('eng_projects')->orderByDesc('id')->limit(6)->get(),
            'tasks' => DB::table('eng_tasks')->whereNotIn('status', ['done'])->orderByRaw('due_date is null')->orderBy('due_date')->limit(6)->get(),
            'audits' => $this->auditQuery()->limit(10)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('back.engineering.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
    }

    public function create(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = new \stdClass;
        foreach ($definition['fields'] as $field => $meta) {
            // Only identifiers are accepted from linked workflow actions.
            $record->$field = $meta['type'] === 'relation' ? $request->integer($field) ?: null : null;
        }
        if ($module === 'payments' && ! empty($record->invoice_id)) {
            $invoice = DB::table('eng_invoices')->find($record->invoice_id);
            if ($invoice) {
                $record->amount = max(0, (float) $invoice->amount - (float) DB::table('eng_payments')->where('invoice_id', $invoice->id)->sum('amount'));
            }
        }
        if ($module === 'invoices' && ! empty($record->quote_id)) {
            $quote = DB::table('eng_quotes')->find($record->quote_id);
            if ($quote) {
                $record->client_id = $quote->client_id;
                $record->project_id = $quote->project_id;
                $record->amount = $quote->amount;
            }
        }
        if ($module === 'projects' && ! empty($record->opportunity_id)) {
            $opportunity = DB::table('eng_opportunities')->find($record->opportunity_id);
            if ($opportunity) {
                $record->client_id = $opportunity->client_id;
                $record->name = $opportunity->name;
                $record->budget = $opportunity->amount;
            }
        }

        return view('back.engineering.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('eng_'.$module)->find($record);
        abort_unless($record, 404);

        return view('back.engineering.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('eng_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('engineering.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'eng_'.$module) {
                    $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('eng_'.$key)->where($field, $record->id)->count()];
                }
            }
        }
        $paid = $module === 'invoices' ? DB::table('eng_payments')->where('invoice_id', $record->id)->sum('amount') : null;

        return view('back.engineering.show', compact('module', 'definition', 'record', 'related', 'paid') + [
            'lookups' => $this->lookups($definition),
            'audits' => $this->auditQuery()->where('module', $module)->where('record_id', $record->id)->limit(20)->get(),
        ]);
    }

    private function validated(Request $request, string $module, ?int $id): array
    {
        $rules = [];
        foreach ($this->definition($module)['fields'] as $field => $meta) {
            $rule = [$meta['required'] ? 'required' : 'nullable'];
            $rule = array_merge($rule, match ($meta['type']) {
                'relation' => ['integer', Rule::exists($meta['table'], 'id')],
                'select' => [Rule::in(array_keys($meta['options']))],
                'date' => ['date_format:Y-m-d'],
                'money' => ['numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
                'number' => ['integer', 'min:'.($meta['min'] ?? 0), 'max:'.($meta['max'] ?? 100000)],
                'email' => ['email', 'max:255'],
                'url' => ['url:http,https', 'max:255'],
                'textarea' => ['string', 'max:20000'],
                default => ['string', 'max:255'],
            });
            if (($meta['unique'] ?? false) || ($module === 'team' && $field === 'user_id')) {
                $rule[] = Rule::unique('eng_'.$module, $field)->ignore($id);
            }
            $rules[$field] = $rule;
        }
        $data = $request->validate($rules);
        $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
        if ($module === 'deliverables' && ($data['status'] ?? null) === 'accepted') {
            $manager = DB::table('eng_team')->where('user_id', $request->user()->id)->where('status', 'active')->where('role', 'manager')->exists();
            if (! $this->administrative($request) && ! $manager) {
                $fail('status', 'La réception d’un livrable doit être validée par un responsable de la chambre.');
            }
            if (empty($data['document_url'])) {
                $fail('document_url', 'Ajoutez le lien du rendu avant de valider le livrable.');
            }
        }
        if (! empty($data['start_date']) && ! empty($data['due_date']) && $data['due_date'] < $data['start_date']) {
            $fail('due_date', 'L’échéance doit suivre la date de début.');
        }
        // Do not allow linking a contact, project or opportunity from another client.
        foreach (['contact_id' => 'eng_contacts', 'project_id' => 'eng_projects', 'opportunity_id' => 'eng_opportunities', 'quote_id' => 'eng_quotes'] as $field => $table) {
            if (! empty($data[$field]) && ! empty($data['client_id'])) {
                $linked = DB::table($table)->find($data[$field]);
                if ($linked->client_id != $data['client_id']) {
                    $fail($field, 'Cet élément appartient à un autre client.');
                }
                if ($field === 'quote_id' && $linked->status !== 'accepted') {
                    $fail($field, 'Seul un devis accepté peut être facturé.');
                }
                if ($field === 'quote_id' && $linked->project_id && ($data['project_id'] ?? null) != $linked->project_id) {
                    $fail('project_id', 'Le projet doit correspondre au devis.');
                }
            }
        }
        if (! empty($data['task_id']) && DB::table('eng_tasks')->where('id', $data['task_id'])->value('project_id') != $data['project_id']) {
            $fail('task_id', 'La tâche doit appartenir au projet sélectionné.');
        }
        if (in_array($module, ['quotes', 'invoices'], true) && $this->cents($data['amount']) <= 0) {
            $fail('amount', 'Le montant du document doit être supérieur à zéro.');
        }
        foreach (['owner_id', 'assignee_id'] as $field) {
            if (! empty($data[$field])) {
                $privileged = DB::table('users')->where('id', $data[$field])->whereIn('role', ['admin', 'superadmin'])->exists();
                $member = DB::table('eng_team')->where('user_id', $data[$field])->where('status', 'active')->exists();
                if (! $privileged && ! $member) {
                    $fail($field, 'Choisissez un administrateur ou un membre actif de la chambre.');
                }
            }
        }
        if ($module === 'payments') {
            $invoice = DB::table('eng_invoices')->where('id', $data['invoice_id'])->lockForUpdate()->first();
            $paid = DB::table('eng_payments')->where('invoice_id', $invoice->id)->when($id, fn ($q) => $q->where('id', '!=', $id))->sum('amount');
            if ($invoice->status !== 'issued') {
                $fail('invoice_id', 'La facture doit être émise pour recevoir un encaissement.');
            }
            if ($this->cents($data['amount']) <= 0 || $this->cents($data['amount']) + $this->cents($paid) > $this->cents($invoice->amount)) {
                $fail('amount', 'Le paiement doit être positif et ne peut dépasser le solde de la facture.');
            }
        }
        if ($module === 'invoices' && $id) {
            $paid = DB::table('eng_payments')->where('invoice_id', $id)->sum('amount');
            if ($this->cents($paid) > 0 && ($data['status'] !== 'issued' || $this->cents($data['amount']) < $this->cents($paid))) {
                $fail('amount', 'Cette facture a des paiements : conservez son statut émis et un montant au moins égal aux encaissements.');
            }
            $before = DB::table('eng_invoices')->find($id);
            if ($this->cents($paid) > 0 && ($data['client_id'] != $before->client_id || ($data['project_id'] ?? null) != $before->project_id)) {
                $fail('client_id', 'Une facture encaissée ne peut pas être transférée à un autre client ou projet.');
            }
        }

        return $data;
    }

    private function cents($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action,
            'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'ingenierie')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
    }

    public function store(Request $request, string $module)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        $id = DB::transaction(function () use ($request, $module) {
            $this->lockOpen();
            $data = $this->validated($request, $module, null);
            $id = DB::table('eng_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $module, $id, 'create', null, $data);

            return $id;
        });

        return redirect()->route('engineering.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        DB::transaction(function () use ($request, $module, $record) {
            $this->lockOpen();
            $before = DB::table('eng_'.$module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $data = $this->validated($request, $module, $record);
            DB::table('eng_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
            $this->audit($request, $module, $record, 'update', $before, $data);
        });

        return redirect()->route('engineering.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        $this->definition($module);
        abort_unless($this->administrative($request), 403);
        try {
            DB::transaction(function () use ($request, $module, $record) {
                $this->lockOpen();
                $before = DB::table('eng_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                DB::table('eng_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Archivez-le ou retirez ses dépendances avant de le supprimer.']);
            }
            throw $exception;
        }

        return redirect()->route('engineering.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'ingenierie')
            ->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function auditLog()
    {
        return view('back.engineering.audit', ['audits' => $this->auditQuery()->paginate(30)]);
    }

    public function reports()
    {
        $projects = DB::table('eng_projects')->select('eng_projects.*')
            ->selectSub(DB::table('eng_invoices')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('project_id', 'eng_projects.id')->where('status', 'issued'), 'billed')
            ->selectSub(DB::table('eng_payments')->join('eng_invoices', 'eng_invoices.id', '=', 'invoice_id')->selectRaw('coalesce(sum(eng_payments.amount), 0)')->whereColumn('eng_invoices.project_id', 'eng_projects.id'), 'collected')
            ->selectSub(DB::table('eng_expenses')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('project_id', 'eng_projects.id'), 'spent')
            ->selectSub(DB::table('eng_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'eng_projects.id'), 'task_count')
            ->selectSub(DB::table('eng_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'eng_projects.id')->where('status', 'done'), 'task_done')
            ->orderByDesc('id')->paginate(25);

        return view('back.engineering.reports', compact('projects'));
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
        }, 'ingenierie-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
