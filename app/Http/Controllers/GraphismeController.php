<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GraphismeController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('graphisme.modules')[$module] ?? null;
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
        $query = DB::table('gfx_'.$module);
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
            $query->where('status', 'issued')->whereRaw('amount > (select coalesce(sum(amount), 0) from gfx_payments where invoice_id = gfx_invoices.id)');
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
                            ->orWhereIn('id', DB::table('gfx_team')->select('user_id')->where('status', 'active'));
                    });
                }
                $lookups[$field] = $query->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }

        return $lookups;
    }

    public function dashboard()
    {
        $collected = DB::table('gfx_payments')->sum('amount');
        $billed = DB::table('gfx_invoices')->where('status', 'issued')->sum('amount');
        $kpis = [
            ['Projets en création', DB::table('gfx_projects')->where('status', 'design')->count(), 'projects', ['status' => 'design']],
            ['Maquettes à valider', DB::table('gfx_designs')->where('status', 'review')->count(), 'designs', ['status' => 'review']],
            ['Versions à valider', DB::table('gfx_revisions')->where('status', 'review')->count(), 'revisions', ['status' => 'review']],
            ['Retours ouverts', DB::table('gfx_feedback')->where('status', 'open')->count(), 'feedback', ['status' => 'open']],
            ['Tâches en retard', DB::table('gfx_tasks')->whereDate('due_date', '<', today())->where('status', '!=', 'done')->count(), 'tasks', ['overdue' => 1]],
            ['Livrables à valider', DB::table('gfx_deliverables')->where('status', 'review')->count(), 'deliverables', ['status' => 'review']],
            ['Encaissements (FCFA)', number_format((float) $collected, 0, ',', ' '), 'payments', []],
            ['Reste à encaisser (FCFA)', number_format(max(0, (float) $billed - (float) $collected), 0, ',', ' '), 'invoices', ['unpaid' => 1]],
        ];
        return view('back.graphisme.dashboard', [
            'kpis' => $kpis, 'modules' => config('graphisme.modules'),
            'projects' => DB::table('gfx_projects')->orderByDesc('id')->limit(6)->get(),
            'feedback' => DB::table('gfx_feedback')->whereIn('status', ['open', 'in_progress'])->orderByRaw('due_date is null')->orderBy('due_date')->limit(6)->get(),
            'audits' => $this->auditQuery()->limit(8)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('back.graphisme.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
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
            $invoice = DB::table('gfx_invoices')->find($record->invoice_id);
            if ($invoice) {
                $record->amount = max(0, (float) $invoice->amount - (float) DB::table('gfx_payments')->where('invoice_id', $invoice->id)->sum('amount'));
            }
        }
        if ($module === 'invoices' && ! empty($record->quote_id)) {
            $quote = DB::table('gfx_quotes')->find($record->quote_id);
            if ($quote) {
                $record->client_id = $quote->client_id;
                $record->project_id = $quote->project_id;
                $record->amount = $quote->amount;
            }
        }
        if (!empty($record->design_id)) {
            $design = DB::table('gfx_designs')->find($record->design_id);
            if ($design && isset($definition['fields']['project_id'])) $record->project_id = $design->project_id;
        }
        if (!empty($record->revision_id)) {
            $revision = DB::table('gfx_revisions')->find($record->revision_id);
            if ($revision) {
                $design = DB::table('gfx_designs')->find($revision->design_id);
                if (isset($definition['fields']['project_id'])) $record->project_id = $design->project_id;
                if (isset($definition['fields']['design_id'])) $record->design_id = $design->id;
                if (isset($definition['fields']['version'])) $record->version = $revision->version;
            }
        }
        return view('back.graphisme.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('gfx_'.$module)->find($record);
        abort_unless($record, 404);

        return view('back.graphisme.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('gfx_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('graphisme.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'gfx_'.$module) {
                    $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('gfx_'.$key)->where($field, $record->id)->count()];
                }
            }
        }
        $paid = $module === 'invoices' ? DB::table('gfx_payments')->where('invoice_id', $record->id)->sum('amount') : null;

        return view('back.graphisme.show', compact('module', 'definition', 'record', 'related', 'paid') + [
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
                $rule[] = Rule::unique('gfx_'.$module, $field)->ignore($id);
            }
            $rules[$field] = $rule;
        }
        foreach ($this->definition($module)['unique_together'] ?? [] as $uniqueField) {
            $unique = Rule::unique('gfx_'.$module, $uniqueField)->ignore($id);
            foreach ($this->definition($module)['unique_together'] as $otherField) {
                if ($otherField !== $uniqueField) $unique->where($otherField, $request->input($otherField));
            }
            $rules[$uniqueField][] = $unique;
        }
        $data = $request->validate($rules);
        $this->validateGraphisme($request, $module, $id, $data);
        $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
        if ($module === 'deliverables' && ($data['status'] ?? null) === 'accepted') {
            $manager = DB::table('gfx_team')->where('user_id', $request->user()->id)->where('status', 'active')->where('role', 'manager')->exists();
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
        foreach (['contact_id' => 'gfx_contacts', 'project_id' => 'gfx_projects', 'quote_id' => 'gfx_quotes'] as $field => $table) {
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
        if (! empty($data['task_id']) && DB::table('gfx_tasks')->where('id', $data['task_id'])->value('project_id') != $data['project_id']) {
            $fail('task_id', 'La tâche doit appartenir au projet sélectionné.');
        }
        if (in_array($module, ['quotes', 'invoices'], true) && $this->cents($data['amount']) <= 0) {
            $fail('amount', 'Le montant du document doit être supérieur à zéro.');
        }
        foreach (['owner_id', 'assignee_id'] as $field) {
            if (! empty($data[$field])) {
                $privileged = DB::table('users')->where('id', $data[$field])->whereIn('role', ['admin', 'superadmin'])->exists();
                $member = DB::table('gfx_team')->where('user_id', $data[$field])->where('status', 'active')->exists();
                if (! $privileged && ! $member) {
                    $fail($field, 'Choisissez un administrateur ou un membre actif de la chambre.');
                }
            }
        }
        if ($module === 'payments') {
            $invoice = DB::table('gfx_invoices')->where('id', $data['invoice_id'])->lockForUpdate()->first();
            $paid = DB::table('gfx_payments')->where('invoice_id', $invoice->id)->when($id, fn ($q) => $q->where('id', '!=', $id))->sum('amount');
            if ($invoice->status !== 'issued') {
                $fail('invoice_id', 'La facture doit être émise pour recevoir un encaissement.');
            }
            if ($this->cents($data['amount']) <= 0 || $this->cents($data['amount']) + $this->cents($paid) > $this->cents($invoice->amount)) {
                $fail('amount', 'Le paiement doit être positif et ne peut dépasser le solde de la facture.');
            }
        }
        if ($module === 'invoices' && $id) {
            $paid = DB::table('gfx_payments')->where('invoice_id', $id)->sum('amount');
            if ($this->cents($paid) > 0 && ($data['status'] !== 'issued' || $this->cents($data['amount']) < $this->cents($paid))) {
                $fail('amount', 'Cette facture a des paiements : conservez son statut émis et un montant au moins égal aux encaissements.');
            }
            $before = DB::table('gfx_invoices')->find($id);
            if ($this->cents($paid) > 0 && ($data['client_id'] != $before->client_id || ($data['project_id'] ?? null) != $before->project_id)) {
                $fail('client_id', 'Une facture encaissée ne peut pas être transférée à un autre client ou projet.');
            }
        }

        return $data;
    }

    private function manager(Request $request): bool
    {
        return $this->administrative($request) || DB::table('gfx_team')->where('user_id', $request->user()->id)->where('role', 'manager')->where('status', 'active')->exists();
    }

    private function validateGraphisme(Request $request, string $module, ?int $id, array $data): void
    {
        $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
        $before = $id ? DB::table('gfx_'.$module)->find($id) : null;
        $projectId = $data['project_id'] ?? null;
        $design = !empty($data['design_id']) ? DB::table('gfx_designs')->find($data['design_id']) : null;
        $revision = !empty($data['revision_id']) ? DB::table('gfx_revisions')->find($data['revision_id']) : null;
        if ($design) $projectId = $design->project_id;
        if ($revision) {
            $sourceDesign = DB::table('gfx_designs')->find($revision->design_id);
            if ($design && $design->id != $sourceDesign->id) {
                $fail('revision_id', 'La version appartient à une autre maquette.');
            }
            if (!empty($data['project_id']) && $data['project_id'] != $sourceDesign->project_id) {
                $fail('revision_id', 'La version appartient à un autre projet.');
            }
            $projectId = $sourceDesign->project_id;
        }
        $project = $projectId ? DB::table('gfx_projects')->find($projectId) : null;
        if ($project && in_array($module, ['briefs', 'identities', 'assets', 'designs', 'revisions', 'feedback', 'tasks', 'deliverables'], true) && in_array($project->status, ['delivered', 'cancelled'], true)) {
            $fail(isset($data['project_id']) ? 'project_id' : 'status', 'Rouvrez le projet avant de modifier son travail créatif ou d’ajouter des corrections.');
        }
        if ($project && !empty($data['client_id']) && $data['client_id'] != $project->client_id) {
            $fail('client_id', 'Ce projet appartient à un autre client.');
        }
        if ($project && !empty($data['contact_id']) && DB::table('gfx_contacts')->where('id', $data['contact_id'])->value('client_id') != $project->client_id) {
            $fail('contact_id', 'Ce contact appartient à un autre client.');
        }
        if (in_array($module, ['briefs', 'identities', 'assets', 'designs', 'revisions', 'deliverables'], true)) {
            $approved = in_array($data['status'], ['approved', 'accepted'], true);
            $wasApproved = $before && in_array($before->status, ['approved', 'accepted'], true);
            if (($approved || $wasApproved) && !$this->manager($request)) {
                $fail('status', 'La validation et les changements de dossiers approuvés sont réservés aux responsables Graphisme.');
            }
        }
        if ($module === 'revisions') {
            if ($before && ($before->status === 'approved' || $before->approved_at)) {
                foreach (['design_id', 'version', 'source_url', 'preview_url', 'change_notes'] as $field) {
                    if (($before->$field ?? null) != ($data[$field] ?? null)) {
                        $fail($field, 'Cette version approuvée est verrouillée. Créez une nouvelle version pour modifier ses fichiers ou son contenu.');
                    }
                }
            }
            if ($data['status'] === 'approved') {
                $pending = DB::table('gfx_feedback')->where('design_id', $data['design_id'])->where('type', 'client')->whereIn('status', ['open', 'in_progress'])
                    ->where(function ($q) use ($id) { $q->whereNull('revision_id'); if ($id) $q->orWhere('revision_id', $id); })->exists();
                if ($pending) $fail('status', 'Traitez les retours client sur cette version avant de l’approuver.');
            }
        }
        if ($module === 'designs' && $data['status'] === 'approved') {
            if (!$id || !DB::table('gfx_revisions')->where('design_id', $id)->where('status', 'approved')->exists()) {
                $fail('status', 'Ajoutez et approuvez une version avant de valider la maquette.');
            }
            if (DB::table('gfx_feedback')->where('design_id', $id)->whereIn('status', ['open', 'in_progress'])->exists()) {
                $fail('status', 'Des retours restent à traiter sur cette maquette.');
            }
            if (in_array($data['kind'], ['print', 'packaging'], true) && empty($data['dimensions'])) {
                $fail('dimensions', 'Précisez les dimensions et unités du support avant validation.');
            }
        }
        if ($module === 'feedback' && $before && $before->type === 'client' && $data['type'] !== 'client' && !$this->manager($request)) {
            $fail('type', 'Un retour client ne peut pas être reclassé en revue interne sans responsable.');
        }
        if ($module === 'feedback' && in_array($data['status'], ['resolved', 'rejected'], true)) {
            if (($data['type'] === 'client' || ($before && $before->type === 'client')) && !$this->manager($request)) {
                $fail('status', 'Un responsable doit clôturer les retours client.');
            }
            if (empty($data['reply'])) $fail('reply', 'Renseignez la réponse ou la justification du traitement.');
        }
        if ($module === 'deliverables' && $data['status'] === 'accepted') {
            if ($revision->status !== 'approved' || $sourceDesign->status !== 'approved') {
                $fail('revision_id', 'La version source et sa maquette doivent être approuvées avant réception du livrable.');
            }
            if ($data['version'] !== $revision->version) $fail('version', 'La version du livrable doit correspondre à celle du fichier source.');
        }
        if ($module === 'projects' && $data['status'] === 'delivered') {
            if (!$id || !DB::table('gfx_deliverables')->where('project_id', $id)->where('status', 'accepted')->exists()) {
                $fail('status', 'Le projet doit avoir au moins un livrable accepté avant sa livraison.');
            }
            if (DB::table('gfx_deliverables')->where('project_id', $id)->where('status', '!=', 'accepted')->exists() || DB::table('gfx_tasks')->where('project_id', $id)->where('status', '!=', 'done')->exists()) {
                $fail('status', 'Terminez les tâches et validez tous les livrables avant de livrer le projet.');
            }
            foreach (DB::table('gfx_designs')->where('project_id', $id)->where('status', '!=', 'archived')->get() as $item) {
                $received = DB::table('gfx_deliverables')->join('gfx_revisions', 'gfx_revisions.id', '=', 'revision_id')
                    ->where('gfx_revisions.design_id', $item->id)->where('gfx_revisions.status', 'approved')->where('gfx_deliverables.status', 'accepted')->exists();
                if ($item->status !== 'approved' || !$received || DB::table('gfx_feedback')->where('design_id', $item->id)->whereIn('status', ['open', 'in_progress'])->exists()) {
                    $fail('status', 'Chaque maquette active doit être approuvée, livrée et sans correction en attente.');
                }
            }
        }
        foreach (['client_id', 'project_id', 'design_id'] as $parent) {
            if ($before && property_exists($before, $parent) && isset($data[$parent]) && $before->$parent != $data[$parent]) {
                foreach (config('graphisme.modules') as $related => $definition) {
                    foreach ($definition['fields'] as $field => $meta) {
                        if (($meta['table'] ?? '') === 'gfx_'.$module && DB::table('gfx_'.$related)->where($field, $id)->exists()) {
                            $fail($parent, 'Ce dossier est déjà utilisé : son rattachement ne peut plus être changé.');
                        }
                    }
                }
            }
        }
    }

    private function syncDesign(Request $request, string $module, int $record, array $data): void
    {
        if (!in_array($module, ['revisions', 'feedback'], true)) return;
        $pending = ($module === 'revisions' && in_array($data['status'], ['review', 'rejected'], true))
            || ($module === 'feedback' && in_array($data['status'], ['open', 'in_progress'], true));
        if (!$pending) return;
        $design = DB::table('gfx_designs')->where('id', $data['design_id'])->first();
        if ($design->status === 'approved') {
            DB::table('gfx_designs')->where('id', $design->id)->update(['status' => 'review', 'updated_at' => now()]);
            $this->audit($request, 'designs', $design->id, 'update', $design, ['status' => 'review']);
        }
    }

    public function gallery(Request $request)
    {
        $request->validate(['project_id' => 'nullable|integer|exists:gfx_projects,id', 'status' => ['nullable', Rule::in(array_keys(config('graphisme.modules.revisions.fields.status.options')))]]);
        $query = DB::table('gfx_revisions')->join('gfx_designs', 'gfx_designs.id', '=', 'design_id')->join('gfx_projects', 'gfx_projects.id', '=', 'project_id')
            ->select('gfx_revisions.*', 'gfx_designs.name as design_name', 'gfx_projects.name as project_name');
        if ($request->filled('project_id')) $query->where('gfx_projects.id', $request->integer('project_id'));
        if ($request->filled('status')) $query->where('gfx_revisions.status', $request->input('status'));
        return view('back.graphisme.gallery', ['revisions' => $query->orderByDesc('gfx_revisions.id')->paginate(24)->withQueryString(), 'projects' => DB::table('gfx_projects')->pluck('name', 'id')]);
    }

    public function planning(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $month = \Carbon\Carbon::createFromFormat('!Y-m', $request->input('month', now()->format('Y-m')));
        $start = $month->copy()->startOfMonth()->format('Y-m-d'); $end = $month->copy()->endOfMonth()->format('Y-m-d');
        $entries = collect();
        foreach (['projects' => 'Projet', 'designs' => 'Maquette', 'tasks' => 'Tâche', 'deliverables' => 'Livrable'] as $module => $label) {
            foreach (DB::table('gfx_'.$module)->whereBetween('due_date', [$start, $end])->orderBy('due_date')->get() as $record) {
                $entries->push(['module' => $module, 'label' => $label, 'record' => $record]);
            }
        }
        return view('back.graphisme.planning', ['entries' => $entries->sortBy(fn ($entry) => $entry['record']->due_date), 'month' => $month]);
    }

    private function cents($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'graphisme', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action,
            'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'graphisme')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'superadmin'], true) || DB::table('gfx_team')->where('user_id', $user->id)->where('status', 'active')->exists(), 403);
    }

    public function store(Request $request, string $module)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        $id = DB::transaction(function () use ($request, $module) {
            $this->lockOpen();
            $data = $this->validated($request, $module, null);
            if ($module === 'revisions' && $data['status'] === 'approved') $data['approved_at'] = now();
            $id = DB::table('gfx_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $module, $id, 'create', null, $data);
            $this->syncDesign($request, $module, $id, $data);

            return $id;
        });

        return redirect()->route('graphisme.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        DB::transaction(function () use ($request, $module, $record) {
            $this->lockOpen();
            $before = DB::table('gfx_'.$module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $data = $this->validated($request, $module, $record);
            if ($module === 'revisions' && $data['status'] === 'approved' && !$before->approved_at) $data['approved_at'] = now();
            DB::table('gfx_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
            $this->audit($request, $module, $record, 'update', $before, $data);
            $this->syncDesign($request, $module, $record, $data);
        });

        return redirect()->route('graphisme.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        $this->definition($module);
        abort_unless($this->administrative($request), 403);
        try {
            DB::transaction(function () use ($request, $module, $record) {
                $this->lockOpen();
                $before = DB::table('gfx_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                DB::table('gfx_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Archivez-le ou retirez ses dépendances avant de le supprimer.']);
            }
            throw $exception;
        }

        return redirect()->route('graphisme.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'graphisme')
            ->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function auditLog()
    {
        return view('back.graphisme.audit', ['audits' => $this->auditQuery()->paginate(30)]);
    }

    public function reports()
    {
        $projects = DB::table('gfx_projects')->select('gfx_projects.*')
            ->selectSub(DB::table('gfx_invoices')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('project_id', 'gfx_projects.id')->where('status', 'issued'), 'billed')
            ->selectSub(DB::table('gfx_payments')->join('gfx_invoices', 'gfx_invoices.id', '=', 'invoice_id')->selectRaw('coalesce(sum(gfx_payments.amount), 0)')->whereColumn('gfx_invoices.project_id', 'gfx_projects.id'), 'collected')
            ->selectSub(DB::table('gfx_expenses')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('project_id', 'gfx_projects.id'), 'spent')
            ->selectSub(DB::table('gfx_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'gfx_projects.id'), 'task_count')
            ->selectSub(DB::table('gfx_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'gfx_projects.id')->where('status', 'done'), 'task_done')
            ->selectSub(DB::table('gfx_designs')->selectRaw('count(*)')->whereColumn('project_id', 'gfx_projects.id'), 'design_count')
            ->selectSub(DB::table('gfx_designs')->selectRaw('count(*)')->whereColumn('project_id', 'gfx_projects.id')->where('status', 'approved'), 'design_approved')
            ->orderByDesc('id')->paginate(25);

        return view('back.graphisme.reports', compact('projects'));
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
        }, 'graphisme-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
