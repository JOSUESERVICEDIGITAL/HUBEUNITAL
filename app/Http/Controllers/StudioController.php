<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudioController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('studio.modules')[$module] ?? null;
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
        $query = DB::table('std_'.$module);
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
            $query->where('status', 'issued')->whereRaw('amount > (select coalesce(sum(amount), 0) from std_payments where invoice_id = std_invoices.id)');
        }

        return $query;
    }

    private function lookups(array $definition): array
    {
        $lookups = [];
        foreach ($definition['fields'] as $field => $meta) {
            if ($meta['type'] === 'relation') {
                $query = DB::table($meta['table']);
                if ($meta['table'] === 'users' && ($field !== 'user_id' || isset($definition['fields']['session_id']))) {
                    $query->where(function ($users) {
                        $users->whereIn('role', ['admin', 'superadmin'])
                            ->orWhereIn('id', DB::table('std_team')->select('user_id')->where('status', 'active'));
                    });
                }
                $lookups[$field] = $query->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }

        return $lookups;
    }

    public function dashboard()
    {
        $collected = DB::table('std_payments')->sum('amount');
        $billed = DB::table('std_invoices')->where('status', 'issued')->sum('amount');
        $kpis = [
            ['Productions en captation', DB::table('std_projects')->where('status', 'shooting')->count(), 'projects', ['status' => 'shooting']],
            ['Séances confirmées', DB::table('std_sessions')->where('status', 'confirmed')->count(), 'sessions', ['status' => 'confirmed']],
            ['Productions en postproduction', DB::table('std_projects')->where('status', 'postproduction')->count(), 'projects', ['status' => 'postproduction']],
            ['Livrables à valider', DB::table('std_deliverables')->where('status', 'review')->count(), 'deliverables', ['status' => 'review']],
            ['Tâches en retard', DB::table('std_tasks')->whereDate('due_date', '<', today())->where('status', '!=', 'done')->count(), 'tasks', ['overdue' => 1]],
            ['Équipements sortis', DB::table('std_reservations')->where('status', 'checked_out')->count(), 'reservations', ['status' => 'checked_out']],
            ['Encaissements (FCFA)', number_format((float) $collected, 0, ',', ' '), 'payments', []],
            ['Reste à encaisser (FCFA)', number_format(max(0, (float) $billed - (float) $collected), 0, ',', ' '), 'invoices', ['unpaid' => 1]],
        ];

        return view('back.studio.dashboard', [
            'kpis' => $kpis, 'modules' => config('studio.modules'),
            'projects' => DB::table('std_projects')->orderByDesc('id')->limit(6)->get(),
            'sessions' => DB::table('std_sessions')->whereIn('status', $this->activeSessions())->where('ends_at', '>=', now(config('studio.timezone'))->format('Y-m-d H:i:s'))->orderBy('starts_at')->limit(6)->get(),
            'audits' => $this->auditQuery()->limit(8)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('back.studio.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
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
            $invoice = DB::table('std_invoices')->find($record->invoice_id);
            if ($invoice) {
                $record->amount = max(0, (float) $invoice->amount - (float) DB::table('std_payments')->where('invoice_id', $invoice->id)->sum('amount'));
            }
        }
        if ($module === 'invoices' && ! empty($record->quote_id)) {
            $quote = DB::table('std_quotes')->find($record->quote_id);
            if ($quote) {
                $record->client_id = $quote->client_id;
                $record->project_id = $quote->project_id;
                $record->amount = $quote->amount;
            }
        }
        if (! empty($record->session_id) && isset($definition['fields']['project_id'])) {
            $session = DB::table('std_sessions')->find($record->session_id);
            if ($session) {
                $record->project_id = $session->project_id;
            }
        }

        return view('back.studio.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('std_'.$module)->find($record);
        abort_unless($record, 404);

        return view('back.studio.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('std_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('studio.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'std_'.$module) {
                    $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('std_'.$key)->where($field, $record->id)->count()];
                }
            }
        }
        $paid = $module === 'invoices' ? DB::table('std_payments')->where('invoice_id', $record->id)->sum('amount') : null;

        return view('back.studio.show', compact('module', 'definition', 'record', 'related', 'paid') + [
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
                'datetime' => ['date_format:Y-m-d\\TH:i'],
                'money' => ['numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
                'number' => ['integer', 'min:'.($meta['min'] ?? 0), 'max:'.($meta['max'] ?? 100000)],
                'email' => ['email', 'max:255'],
                'url' => ['url:http,https', 'max:255'],
                'textarea' => ['string', 'max:20000'],
                default => ['string', 'max:255'],
            });
            if (($meta['unique'] ?? false) || ($module === 'team' && $field === 'user_id')) {
                $rule[] = Rule::unique('std_'.$module, $field)->ignore($id);
            }
            $rules[$field] = $rule;
        }
        foreach ($this->definition($module)['unique_together'] ?? [] as $uniqueField) {
            $unique = Rule::unique('std_'.$module, $uniqueField)->ignore($id);
            foreach ($this->definition($module)['unique_together'] as $otherField) {
                if ($otherField !== $uniqueField) {
                    $unique->where($otherField, $request->input($otherField));
                }
            }
            $rules[$uniqueField][] = $unique;
        }
        $data = $request->validate($rules);
        foreach ($this->definition($module)['fields'] as $field => $meta) {
            if ($meta['type'] === 'datetime' && ! empty($data[$field])) {
                $data[$field] = \Carbon\Carbon::createFromFormat('Y-m-d\\TH:i', $data[$field], config('studio.timezone'))->format('Y-m-d H:i:s');
            }
        }
        $this->validateStudio($request, $module, $id, $data);
        $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
        if ($module === 'deliverables' && ($data['status'] ?? null) === 'accepted') {
            $manager = DB::table('std_team')->where('user_id', $request->user()->id)->where('status', 'active')->where('role', 'manager')->exists();
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
        foreach (['contact_id' => 'std_contacts', 'project_id' => 'std_projects', 'quote_id' => 'std_quotes'] as $field => $table) {
            if (! empty($data[$field]) && ! empty($data['client_id'])) {
                $linked = DB::table($table)->find($data[$field]);
                if ($linked->client_id != $data['client_id']) {
                    $fail($field, 'Cet élément appartient à un autre client.');
                }
                if ($field === 'quote_id' && $linked->status !== 'accepted') {
                    $fail($field, 'Seul un devis accepté peut être facturé.');
                }
                if ($field === 'quote_id' && $linked->project_id && ($data['project_id'] ?? null) != $linked->project_id) {
                    $fail('project_id', 'La production doit correspondre au devis.');
                }
            }
        }
        if (! empty($data['task_id']) && DB::table('std_tasks')->where('id', $data['task_id'])->value('project_id') != $data['project_id']) {
            $fail('task_id', 'La tâche doit appartenir à la production sélectionnée.');
        }
        if (in_array($module, ['quotes', 'invoices'], true) && $this->cents($data['amount']) <= 0) {
            $fail('amount', 'Le montant du document doit être supérieur à zéro.');
        }
        foreach (['owner_id', 'assignee_id'] as $field) {
            if (! empty($data[$field])) {
                $privileged = DB::table('users')->where('id', $data[$field])->whereIn('role', ['admin', 'superadmin'])->exists();
                $member = DB::table('std_team')->where('user_id', $data[$field])->where('status', 'active')->exists();
                if (! $privileged && ! $member) {
                    $fail($field, 'Choisissez un administrateur ou un membre actif de la chambre.');
                }
            }
        }
        if ($module === 'payments') {
            $invoice = DB::table('std_invoices')->where('id', $data['invoice_id'])->lockForUpdate()->first();
            $paid = DB::table('std_payments')->where('invoice_id', $invoice->id)->when($id, fn ($q) => $q->where('id', '!=', $id))->sum('amount');
            if ($invoice->status !== 'issued') {
                $fail('invoice_id', 'La facture doit être émise pour recevoir un encaissement.');
            }
            if ($this->cents($data['amount']) <= 0 || $this->cents($data['amount']) + $this->cents($paid) > $this->cents($invoice->amount)) {
                $fail('amount', 'Le paiement doit être positif et ne peut dépasser le solde de la facture.');
            }
        }
        if ($module === 'invoices' && $id) {
            $paid = DB::table('std_payments')->where('invoice_id', $id)->sum('amount');
            if ($this->cents($paid) > 0 && ($data['status'] !== 'issued' || $this->cents($data['amount']) < $this->cents($paid))) {
                $fail('amount', 'Cette facture a des paiements : conservez son statut émis et un montant au moins égal aux encaissements.');
            }
            $before = DB::table('std_invoices')->find($id);
            if ($this->cents($paid) > 0 && ($data['client_id'] != $before->client_id || ($data['project_id'] ?? null) != $before->project_id)) {
                $fail('client_id', 'Une facture encaissée ne peut pas être transférée à un autre client ou à une autre production.');
            }
        }

        return $data;
    }

    private function activeSessions(): array
    {
        return ['planned', 'confirmed', 'in_progress'];
    }

    private function manager(Request $request): bool
    {
        return $this->administrative($request) || DB::table('std_team')->where('user_id', $request->user()->id)->where('role', 'manager')->where('status', 'active')->exists();
    }

    private function activeUser(int $user): bool
    {
        return DB::table('users')->where('id', $user)->whereIn('role', ['admin', 'superadmin'])->exists()
            || DB::table('std_team')->where('user_id', $user)->where('status', 'active')->exists();
    }

    private function overlaps($query, object $session)
    {
        return $query->whereIn('occupied.status', $this->activeSessions())
            ->where('occupied.starts_at', '<', $session->ends_at)
            ->where('occupied.ends_at', '>', $session->starts_at);
    }

    private function equipmentConflict(int $equipment, object $session, ?int $reservation = null): bool
    {
        $query = DB::table('std_reservations')->join('std_sessions as occupied', 'occupied.id', '=', 'std_reservations.session_id')
            ->where('std_reservations.equipment_id', $equipment)->whereIn('std_reservations.status', ['reserved', 'checked_out'])
            ->when($reservation, fn ($q) => $q->where('std_reservations.id', '!=', $reservation));

        return $this->overlaps($query, $session)->exists();
    }

    private function crewConflict(int $user, object $session, ?int $assignment = null): bool
    {
        $query = DB::table('std_assignments')->join('std_sessions as occupied', 'occupied.id', '=', 'std_assignments.session_id')
            ->where('std_assignments.user_id', $user)->where('std_assignments.status', '!=', 'cancelled')
            ->when($assignment, fn ($q) => $q->where('std_assignments.id', '!=', $assignment));

        return $this->overlaps($query, $session)->exists();
    }

    private function validateStudio(Request $request, string $module, ?int $id, array $data): void
    {
        $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
        $before = $id ? DB::table('std_'.$module)->find($id) : null;
        foreach (['owner_id', 'assignee_id'] as $field) {
            if (! empty($data[$field]) && ! $this->activeUser((int) $data[$field])) {
                $fail($field, 'Choisissez un administrateur ou un membre actif du Studio.');
            }
        }
        if (! empty($data['session_id']) && ! empty($data['project_id'])) {
            if (DB::table('std_sessions')->where('id', $data['session_id'])->value('project_id') != $data['project_id']) {
                $fail('session_id', 'La séance appartient à une autre production.');
            }
        }
        if ($module === 'briefs' && ! empty($data['contact_id'])) {
            $client = DB::table('std_projects')->where('id', $data['project_id'])->value('client_id');
            if (DB::table('std_contacts')->where('id', $data['contact_id'])->value('client_id') != $client) {
                $fail('contact_id', 'Ce contact appartient à un autre client.');
            }
        }
        if (in_array($module, ['briefs', 'assets', 'deliverables'], true)) {
            $approved = in_array($data['status'], ['approved', 'accepted'], true);
            $wasApproved = $before && in_array($before->status, ['approved', 'accepted'], true);
            if (($approved || $wasApproved) && ! $this->manager($request)) {
                $fail('status', 'La validation et la modification des rendus validés sont réservées aux responsables Studio.');
            }
        }
        if ($module === 'projects' && $data['status'] === 'delivered') {
            if (! $id || ! DB::table('std_deliverables')->where('project_id', $id)->where('status', 'accepted')->exists()) {
                $fail('status', 'Une production livrée doit avoir au moins un livrable accepté.');
            }
            if (DB::table('std_deliverables')->where('project_id', $id)->where('status', '!=', 'accepted')->exists()) {
                $fail('status', 'Validez tous les livrables de la production avant sa livraison.');
            }
            if (DB::table('std_sessions')->where('project_id', $id)->whereIn('status', $this->activeSessions())->exists()) {
                $fail('status', 'Terminez ou annulez les séances avant de livrer la production.');
            }
            if (DB::table('std_tasks')->where('project_id', $id)->where('status', '!=', 'done')->exists()) {
                $fail('status', 'Terminez les tâches de postproduction avant la livraison.');
            }
        }
        if ($module === 'projects' && $data['status'] === 'cancelled' && $id && DB::table('std_sessions')->where('project_id', $id)->whereIn('status', $this->activeSessions())->exists()) {
            $fail('status', 'Clôturez ou annulez les séances et régularisez le matériel avant d’annuler la production.');
        }
        if ($module === 'sessions') {
            if ($data['ends_at'] <= $data['starts_at']) {
                $fail('ends_at', 'La fin de séance doit être postérieure à son début.');
            }
            $session = (object) ($data + ['id' => $id]);
            if (in_array($data['status'], $this->activeSessions(), true)) {
                if (DB::table('std_locations')->where('id', $data['location_id'])->value('status') !== 'active') {
                    $fail('location_id', 'Ce lieu est indisponible.');
                }
                $query = DB::table('std_sessions as occupied')->where('location_id', $data['location_id'])->when($id, fn ($q) => $q->where('occupied.id', '!=', $id));
                if ($this->overlaps($query, $session)->exists()) {
                    $fail('starts_at', 'Ce lieu est déjà réservé sur cette plage horaire.');
                }
                if ($id) {
                    foreach (DB::table('std_reservations')->where('session_id', $id)->whereIn('status', ['reserved', 'checked_out'])->get() as $reservation) {
                        if ($this->equipmentConflict($reservation->equipment_id, $session, $reservation->id)) {
                            $fail('starts_at', 'Un équipement réservé est déjà utilisé sur la nouvelle plage horaire.');
                        }
                    }
                    foreach (DB::table('std_assignments')->where('session_id', $id)->where('status', '!=', 'cancelled')->get() as $assignment) {
                        if ($this->crewConflict($assignment->user_id, $session, $assignment->id)) {
                            $fail('starts_at', 'Un membre de l’équipe est affecté à une autre séance sur la nouvelle plage horaire.');
                        }
                    }
                }
            }
            if ($id && in_array($data['status'], ['completed', 'cancelled'], true)) {
                $statuses = $data['status'] === 'completed' ? ['reserved', 'checked_out'] : ['checked_out'];
                if (DB::table('std_reservations')->where('session_id', $id)->whereIn('status', $statuses)->exists()) {
                    $fail('status', 'Enregistrez le retour du matériel sorti et régularisez les réservations avant de clôturer la séance.');
                }
            }
        }
        if (in_array($module, ['reservations', 'assignments'], true)) {
            $session = DB::table('std_sessions')->find($data['session_id']);
            if ($module === 'reservations') {
                if ($before && $before->status === 'checked_out' && ($before->equipment_id != $data['equipment_id'] || $before->session_id != $data['session_id'])) {
                    $fail('equipment_id', 'Enregistrez le retour avant de changer la séance ou le matériel.');
                }
                if ($before && $before->status === 'checked_out' && $data['status'] === 'cancelled') {
                    $fail('status', 'Un matériel sorti doit être retourné avant annulation.');
                }
                if (in_array($data['status'], ['reserved', 'checked_out'], true)) {
                    if (! in_array($session->status, $this->activeSessions(), true)) {
                        $fail('session_id', 'Une séance terminée ou annulée ne peut pas réserver de matériel.');
                    }
                    if (DB::table('std_equipment')->where('id', $data['equipment_id'])->value('status') !== 'available') {
                        $fail('equipment_id', 'Ce matériel est en maintenance ou retiré du service.');
                    }
                    if ($this->equipmentConflict((int) $data['equipment_id'], $session, $id)) {
                        $fail('equipment_id', 'Ce matériel est déjà réservé pour une séance qui se chevauche.');
                    }
                    if ($data['status'] === 'checked_out' && DB::table('std_reservations')->where('equipment_id', $data['equipment_id'])->where('status', 'checked_out')->when($id, fn ($q) => $q->where('id', '!=', $id))->exists()) {
                        $fail('equipment_id', 'Ce matériel n’a pas encore été retourné d’une autre séance.');
                    }
                }
            } else {
                if ($data['status'] !== 'cancelled') {
                    if (! $this->activeUser((int) $data['user_id'])) {
                        $fail('user_id', 'Ce collaborateur ne fait pas partie de l’équipe Studio active.');
                    }
                    if ($session->status === 'cancelled') {
                        $fail('session_id', 'Cette séance est annulée.');
                    }
                    if ($this->crewConflict((int) $data['user_id'], $session, $id)) {
                        $fail('user_id', 'Ce collaborateur est déjà affecté à une séance qui se chevauche.');
                    }
                }
            }
        }
        if ($module === 'equipment' && $data['status'] !== 'available' && $id) {
            $held = DB::table('std_reservations')->join('std_sessions', 'std_sessions.id', '=', 'session_id')->where('equipment_id', $id)
                ->whereIn('std_reservations.status', ['reserved', 'checked_out'])->whereIn('std_sessions.status', $this->activeSessions())->exists();
            if ($held) {
                $fail('status', 'Régularisez les réservations et retours avant de retirer ce matériel du service.');
            }
        }
        if ($module === 'locations' && $data['status'] !== 'active' && $id && DB::table('std_sessions')->where('location_id', $id)->whereIn('status', $this->activeSessions())->exists()) {
            $fail('status', 'Déplacez ou annulez les séances réservées avant de rendre le lieu indisponible.');
        }
        // Preserve links between production, client and source media after records are used.
        foreach (['client_id', 'project_id'] as $parent) {
            if ($before && property_exists($before, $parent) && isset($data[$parent]) && $data[$parent] != $before->$parent) {
                foreach (config('studio.modules') as $related => $definition) {
                    foreach ($definition['fields'] as $field => $meta) {
                        if (($meta['table'] ?? '') === 'std_'.$module && DB::table('std_'.$related)->where($field, $id)->exists()) {
                            $fail($parent, 'Ce dossier est déjà utilisé par d’autres enregistrements : son rattachement ne peut plus être modifié.');
                        }
                    }
                }
            }
        }
    }

    private function syncSession(Request $request, string $module, int $record, array $data): void
    {
        if ($module !== 'sessions' || ! in_array($data['status'], ['completed', 'cancelled'], true)) {
            return;
        }
        if ($data['status'] === 'cancelled') {
            foreach (DB::table('std_reservations')->where('session_id', $record)->where('status', 'reserved')->get() as $before) {
                DB::table('std_reservations')->where('id', $before->id)->update(['status' => 'cancelled', 'updated_at' => now()]);
                $this->audit($request, 'reservations', $before->id, 'update', $before, ['status' => 'cancelled']);
            }
        }
        foreach (DB::table('std_assignments')->where('session_id', $record)->where('status', '!=', 'cancelled')->get() as $before) {
            $status = $data['status'] === 'completed' ? 'done' : 'cancelled';
            if ($before->status !== $status) {
                DB::table('std_assignments')->where('id', $before->id)->update(['status' => $status, 'updated_at' => now()]);
                $this->audit($request, 'assignments', $before->id, 'update', $before, ['status' => $status]);
            }
        }
    }

    public function planning(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $month = \Carbon\Carbon::createFromFormat('!Y-m', $request->input('month', now(config('studio.timezone'))->format('Y-m')), config('studio.timezone'));
        $sessions = DB::table('std_sessions')->join('std_projects', 'std_projects.id', '=', 'project_id')->join('std_locations', 'std_locations.id', '=', 'location_id')
            ->select('std_sessions.*', 'std_projects.name as project_name', 'std_locations.name as location_name')
            ->where('starts_at', '<', $month->copy()->addMonth()->startOfMonth()->format('Y-m-d H:i:s'))
            ->where('ends_at', '>', $month->copy()->startOfMonth()->format('Y-m-d H:i:s'))
            ->orderBy('starts_at')->get();

        return view('back.studio.planning', compact('sessions', 'month'));
    }

    private function cents($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'studio', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action,
            'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'studio')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
        $user = auth()->user();
        abort_unless(in_array($user->role, ['admin', 'superadmin'], true) || DB::table('std_team')->where('user_id', $user->id)->where('status', 'active')->exists(), 403);
    }

    public function store(Request $request, string $module)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        $id = DB::transaction(function () use ($request, $module) {
            $this->lockOpen();
            $data = $this->validated($request, $module, null);
            $id = DB::table('std_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $module, $id, 'create', null, $data);

            return $id;
        });

        return redirect()->route('studio.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        DB::transaction(function () use ($request, $module, $record) {
            $this->lockOpen();
            $before = DB::table('std_'.$module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $data = $this->validated($request, $module, $record);
            DB::table('std_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
            $this->audit($request, $module, $record, 'update', $before, $data);
            $this->syncSession($request, $module, $record, $data);
        });

        return redirect()->route('studio.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        $this->definition($module);
        abort_unless($this->administrative($request), 403);
        try {
            DB::transaction(function () use ($request, $module, $record) {
                $this->lockOpen();
                $before = DB::table('std_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                DB::table('std_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Archivez-le ou retirez ses dépendances avant de le supprimer.']);
            }
            throw $exception;
        }

        return redirect()->route('studio.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'studio')
            ->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function auditLog()
    {
        return view('back.studio.audit', ['audits' => $this->auditQuery()->paginate(30)]);
    }

    public function reports()
    {
        $projects = DB::table('std_projects')->select('std_projects.*')
            ->selectSub(DB::table('std_invoices')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('project_id', 'std_projects.id')->where('status', 'issued'), 'billed')
            ->selectSub(DB::table('std_payments')->join('std_invoices', 'std_invoices.id', '=', 'invoice_id')->selectRaw('coalesce(sum(std_payments.amount), 0)')->whereColumn('std_invoices.project_id', 'std_projects.id'), 'collected')
            ->selectSub(DB::table('std_expenses')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('project_id', 'std_projects.id'), 'spent')
            ->selectSub(DB::table('std_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'std_projects.id'), 'task_count')
            ->selectSub(DB::table('std_tasks')->selectRaw('count(*)')->whereColumn('project_id', 'std_projects.id')->where('status', 'done'), 'task_done')
            ->orderByDesc('id')->paginate(25);

        return view('back.studio.reports', compact('projects'));
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
        }, 'studio-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
