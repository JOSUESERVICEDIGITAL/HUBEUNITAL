<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketingController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('marketing.modules')[$module] ?? null;
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
        $query = DB::table('mkt_'.$module);
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

        if ($module === 'results') {
            $request->validate(['from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d']);
            if ($request->filled('from')) {
                $query->whereDate('recorded_on', '>=', $request->input('from'));
            }
            if ($request->filled('to')) {
                $query->whereDate('recorded_on', '<=', $request->input('to'));
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
                            ->orWhereIn('id', DB::table('mkt_team')->select('user_id')->where('status', 'active'));
                    });
                }
                $lookups[$field] = $query->orderBy($meta['display'])->pluck($meta['display'], 'id')->all();
            }
        }

        return $lookups;
    }

    public function dashboard()
    {
        $totals = $this->resultTotals();
        $kpis = [
            ['Campagnes en cours', DB::table('mkt_campaigns')->where('status', 'active')->count(), 'campaigns', ['status' => 'active']],
            ['Prospects qualifiés', DB::table('mkt_leads')->where('status', 'qualified')->count(), 'leads', ['status' => 'qualified']],
            ['Contenus à valider', DB::table('mkt_contents')->where('status', 'review')->count(), 'contents', ['status' => 'review']],
            ['Tâches en retard', DB::table('mkt_tasks')->whereDate('due_date', '<', today())->where('status', '!=', 'done')->count(), 'tasks', ['overdue' => 1]],
            ['Dépenses média (FCFA)', number_format((float) $totals->spend, 0, ',', ' '), 'results', []],
            ['Conversions mesurées', $totals->conversions, 'results', []],
            ['Revenus attribués (FCFA)', number_format((float) $totals->revenue, 0, ',', ' '), 'results', []],
            ['Coût par prospect (FCFA)', $totals->leads ? number_format($totals->spend / $totals->leads, 2, ',', ' ') : '—', 'results', []],
        ];

        return view('back.marketing.dashboard', [
            'kpis' => $kpis, 'totals' => $totals, 'modules' => config('marketing.modules'),
            'campaigns' => DB::table('mkt_campaigns')->orderByDesc('id')->limit(6)->get(),
            'contents' => DB::table('mkt_contents')->whereIn('status', ['approved', 'scheduled', 'review'])->orderByRaw('due_date is null')->orderBy('due_date')->limit(6)->get(),
            'audits' => $this->auditQuery()->limit(8)->get(),
        ]);
    }

    public function index(Request $request, string $module)
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $module)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('back.marketing.index', compact('module', 'definition', 'records') + ['lookups' => $this->lookups($definition)]);
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
        if (! empty($record->campaign_id)) {
            $campaign = DB::table('mkt_campaigns')->find($record->campaign_id);
            if ($campaign && $module !== 'leads' && array_key_exists('client_id', $definition['fields'])) {
                $record->client_id = $campaign->client_id;
            }
        }

        return view('back.marketing.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function edit(Request $request, string $module, int $record)
    {
        $definition = $this->definition($module);
        $this->mayWrite($request, $module);
        $record = DB::table('mkt_'.$module)->find($record);
        abort_unless($record, 404);

        return view('back.marketing.form', compact('module', 'definition', 'record') + ['lookups' => $this->lookups($definition)]);
    }

    public function show(string $module, int $record)
    {
        $definition = $this->definition($module);
        $record = DB::table('mkt_'.$module)->find($record);
        abort_unless($record, 404);
        $related = [];
        foreach (config('marketing.modules') as $key => $meta) {
            foreach ($meta['fields'] as $field => $fieldMeta) {
                if (($fieldMeta['table'] ?? '') === 'mkt_'.$module) {
                    $related[] = ['module' => $key, 'label' => $meta['label'], 'field' => $field, 'count' => DB::table('mkt_'.$key)->where($field, $record->id)->count()];
                }
            }
        }

        return view('back.marketing.show', compact('module', 'definition', 'record', 'related') + [
            'totals' => $module === 'campaigns' ? $this->resultTotals($record->id) : null,
            'production' => $module === 'campaigns' ? DB::table('mkt_expenses')->where('campaign_id', $record->id)->sum('amount') : null,
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
                'number' => ['integer', 'min:'.($meta['min'] ?? 0), 'max:'.($meta['max'] ?? 2000000000)],
                'email' => ['email', 'max:255'],
                'url' => ['url:http,https', 'max:255'],
                'textarea' => ['string', 'max:20000'],
                default => ['string', 'max:255'],
            });
            if (($meta['unique'] ?? false) || ($module === 'team' && $field === 'user_id')) {
                $rule[] = Rule::unique('mkt_'.$module, $field)->ignore($id);
            }
            $rules[$field] = $rule;
        }
        if ($module === 'results') {
            $rules['recorded_on'][] = Rule::unique('mkt_results', 'recorded_on')
                ->where('campaign_id', $request->input('campaign_id'))
                ->where('channel_id', $request->input('channel_id'))->ignore($id);
        }
        $data = $request->validate($rules);

        return $this->validateMarketing($request, $module, $id, $data);
    }

    private function manager(Request $request): bool
    {
        return $this->administrative($request) || DB::table('mkt_team')
            ->where('user_id', $request->user()->id)->where('status', 'active')->where('role', 'manager')->exists();
    }

    private function validateMarketing(Request $request, string $module, ?int $id, array $data): array
    {
        $fail = fn ($field, $message) => throw ValidationException::withMessages([$field => $message]);
        $before = $id ? DB::table('mkt_'.$module)->find($id) : null;
        if (! empty($data['start_date']) && ! empty($data['due_date']) && $data['due_date'] < $data['start_date']) {
            $fail('due_date', 'La fin de la campagne doit suivre son début.');
        }
        foreach (['owner_id', 'assignee_id'] as $field) {
            if (! empty($data[$field])) {
                $allowed = DB::table('users')->where('id', $data[$field])->whereIn('role', ['admin', 'superadmin'])->exists()
                    || DB::table('mkt_team')->where('user_id', $data[$field])->where('status', 'active')->exists();
                if (! $allowed) {
                    $fail($field, 'Choisissez un administrateur ou un membre actif de Marketing.');
                }
            }
        }
        if (! empty($data['campaign_id'])) {
            $campaign = DB::table('mkt_campaigns')->find($data['campaign_id']);
            // The converted lead's client differs from the campaign's commissioning client.
            if ($module !== 'leads' && array_key_exists('client_id', $this->definition($module)['fields'])) {
                if (! empty($data['client_id']) && $data['client_id'] != $campaign->client_id) {
                    $fail('client_id', 'La campagne appartient à un autre client.');
                }
                $data['client_id'] = $campaign->client_id;
            }
            foreach (['content_id' => 'mkt_contents', 'lead_id' => 'mkt_leads'] as $field => $table) {
                if (! empty($data[$field]) && DB::table($table)->where('id', $data[$field])->value('campaign_id') != $campaign->id) {
                    $fail($field, 'Cet élément appartient à une autre campagne.');
                }
            }
        }
        if (! empty($data['client_id'])) {
            foreach (['strategy_id' => 'mkt_strategies', 'audience_id' => 'mkt_audiences', 'contact_id' => 'mkt_contacts'] as $field => $table) {
                if (! empty($data[$field]) && DB::table($table)->where('id', $data[$field])->value('client_id') != $data['client_id']) {
                    $fail($field, 'Cet élément appartient à un autre client.');
                }
            }
        }
        if ($before && in_array($module, ['campaigns', 'strategies', 'audiences'], true) && $before->client_id != $data['client_id']) {
            foreach (config('marketing.modules') as $related => $definition) {
                foreach ($definition['fields'] as $field => $meta) {
                    if (($meta['table'] ?? '') === 'mkt_'.$module && DB::table('mkt_'.$related)->where($field, $id)->exists()) {
                        $fail('client_id', 'Ce dossier est déjà utilisé. Son client ne peut plus être changé.');
                    }
                }
            }
        }
        if (in_array($module, ['contents', 'strategies', 'assets'], true)) {
            $approved = in_array($data['status'], ['approved', 'scheduled', 'published'], true);
            $oldApproved = $before && in_array($before->status, ['approved', 'scheduled', 'published'], true);
            if (($approved || $oldApproved) && ! $this->manager($request)) {
                $fail('status', 'Les contenus approuvés et leur diffusion sont réservés aux responsables Marketing.');
            }
            if ($module === 'contents' && $approved && empty($data['content_url'])) {
                $fail('content_url', 'Ajoutez le lien du rendu avant validation ou diffusion.');
            }
            if ($module === 'contents' && $data['status'] === 'scheduled' && empty($data['due_date'])) {
                $fail('due_date', 'Renseignez la date de diffusion prévue.');
            }
            if ($module === 'contents' && $data['status'] === 'published') {
                if (empty($data['published_on']) || $data['published_on'] > today()->format('Y-m-d')) {
                    $fail('published_on', 'Une publication doit avoir une date réelle passée ou égale à aujourd’hui.');
                }
            }
            if ($module === 'contents' && $data['status'] !== 'published' && ! empty($data['published_on'])) {
                $fail('published_on', 'La date réelle de publication est réservée au statut Publié.');
            }
        }
        if ($module === 'results') {
            if ($data['recorded_on'] > today()->format('Y-m-d')) {
                $fail('recorded_on', 'Un relevé de résultats ne peut pas être daté dans le futur.');
            }
            if ($data['clicks'] > $data['impressions']) {
                $fail('clicks', 'Le nombre de clics ne peut pas dépasser les impressions.');
            }
            if ($data['conversions'] > $data['leads']) {
                $fail('conversions', 'Les conversions ne peuvent pas dépasser les prospects mesurés.');
            }
        }
        if ($module === 'leads') {
            if ($data['contact_permission'] === 'no' && in_array($data['status'], ['contacted', 'proposal'], true)) {
                $fail('contact_permission', 'Ce prospect s’oppose au contact. Ne planifiez pas de prospection sortante.');
            }
            if ($data['status'] === 'won' && empty($data['client_id'])) {
                $fail('status', 'Associez un client ou utilisez Convertir en client depuis la fiche du prospect.');
            }
            if ($before && $before->client_id && $before->client_id != ($data['client_id'] ?? null)) {
                $fail('client_id', 'Le lien avec un client converti ne peut pas être remplacé.');
            }
        }
        if ($module === 'activities') {
            if (empty($data['client_id']) && empty($data['campaign_id']) && empty($data['lead_id'])) {
                $fail('client_id', 'Associez cet échange à un client, une campagne ou un prospect.');
            }
            if (! empty($data['lead_id']) && $data['type'] !== 'note' && $data['status'] === 'planned') {
                $permission = DB::table('mkt_leads')->where('id', $data['lead_id'])->value('contact_permission');
                if ($permission !== 'yes') {
                    $fail('lead_id', 'L’autorisation de contact de ce prospect doit être confirmée avant une relance.');
                }
            }
        }
        if ($before && $module === 'contents' && $before->campaign_id != $data['campaign_id'] && DB::table('mkt_tasks')->where('content_id', $id)->exists()) {
            $fail('campaign_id', 'Ce contenu est utilisé par des tâches : sa campagne ne peut plus être changée.');
        }

        return $data;
    }

    public function convertLead(Request $request, int $record)
    {
        $clientId = DB::transaction(function () use ($request, $record) {
            $this->lockOpen();
            $lead = DB::table('mkt_leads')->where('id', $record)->lockForUpdate()->first();
            abort_unless($lead, 404);
            $clientId = $lead->client_id;
            if (! $clientId) {
                $clientId = $lead->email ? DB::table('mkt_clients')->where('email', $lead->email)->value('id') : null;
            }
            if (! $clientId) {
                $data = ['name' => $lead->company ?: $lead->name, 'company' => $lead->company, 'email' => $lead->email,
                    'phone' => $lead->phone, 'status' => 'active', 'notes' => 'Client créé depuis le prospect #'.$lead->id,
                    'created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()];
                $clientId = DB::table('mkt_clients')->insertGetId($data);
                $this->audit($request, 'clients', $clientId, 'create', null, $data);
            }
            if ($lead->status !== 'won' || $lead->client_id != $clientId) {
                $after = ['client_id' => $clientId, 'status' => 'won', 'updated_at' => now()];
                DB::table('mkt_leads')->where('id', $lead->id)->update($after);
                $this->audit($request, 'leads', $lead->id, 'update', $lead, $after);
            }

            return $clientId;
        });

        return redirect()->route('marketing.show', ['clients', $clientId])->with('success', 'Le prospect est associé à son dossier client.');
    }

    private function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'marketing', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action,
            'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function lockOpen(): void
    {
        $chamber = Chamber::where('slug', 'marketing')->lockForUpdate()->firstOrFail();
        abort_unless($chamber->is_open, 403, 'La chambre est fermée.');
    }

    public function store(Request $request, string $module)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        $id = DB::transaction(function () use ($request, $module) {
            $this->lockOpen();
            $data = $this->validated($request, $module, null);
            $id = DB::table('mkt_'.$module)->insertGetId($data + ['created_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            $this->audit($request, $module, $id, 'create', null, $data);

            return $id;
        });

        return redirect()->route('marketing.show', [$module, $id])->with('success', 'Enregistrement créé.');
    }

    public function update(Request $request, string $module, int $record)
    {
        $this->definition($module);
        $this->mayWrite($request, $module);
        DB::transaction(function () use ($request, $module, $record) {
            $this->lockOpen();
            $before = DB::table('mkt_'.$module)->where('id', $record)->lockForUpdate()->first();
            abort_unless($before, 404);
            $data = $this->validated($request, $module, $record);
            DB::table('mkt_'.$module)->where('id', $record)->update($data + ['updated_at' => now()]);
            $this->audit($request, $module, $record, 'update', $before, $data);
        });

        return redirect()->route('marketing.show', [$module, $record])->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Request $request, string $module, int $record)
    {
        $this->definition($module);
        abort_unless($this->administrative($request), 403);
        try {
            DB::transaction(function () use ($request, $module, $record) {
                $this->lockOpen();
                $before = DB::table('mkt_'.$module)->where('id', $record)->lockForUpdate()->first();
                abort_unless($before, 404);
                DB::table('mkt_'.$module)->where('id', $record)->delete();
                $this->audit($request, $module, $record, 'delete', $before, null);
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                return back()->withErrors(['delete' => 'Cet élément est utilisé par d’autres dossiers. Archivez-le ou retirez ses dépendances avant de le supprimer.']);
            }
            throw $exception;
        }

        return redirect()->route('marketing.index', $module)->with('success', 'Enregistrement supprimé.');
    }

    private function auditQuery()
    {
        return DB::table('chamber_audits')->leftJoin('users', 'users.id', '=', 'chamber_audits.user_id')
            ->where('chamber_audits.chamber_slug', 'marketing')->select('chamber_audits.*', 'users.name as actor')->orderByDesc('chamber_audits.id');
    }

    public function auditLog()
    {
        return view('back.marketing.audit', ['audits' => $this->auditQuery()->paginate(30)]);
    }

    private function resultTotals(?int $campaign = null)
    {
        return DB::table('mkt_results')->when($campaign, fn ($q) => $q->where('campaign_id', $campaign))
            ->selectRaw('coalesce(sum(impressions), 0) as impressions, coalesce(sum(clicks), 0) as clicks, coalesce(sum(leads), 0) as leads, coalesce(sum(conversions), 0) as conversions, coalesce(sum(spend), 0) as spend, coalesce(sum(revenue), 0) as revenue')->first();
    }

    public function reports(Request $request)
    {
        $request->validate(['client_id' => 'nullable|integer|exists:mkt_clients,id']);
        $query = DB::table('mkt_campaigns')->select('mkt_campaigns.*');
        foreach (['impressions', 'clicks', 'leads', 'conversions', 'spend', 'revenue'] as $field) {
            $query->selectSub(DB::table('mkt_results')->selectRaw('coalesce(sum('.$field.'), 0)')->whereColumn('campaign_id', 'mkt_campaigns.id'), $field);
        }
        $query->selectSub(DB::table('mkt_expenses')->selectRaw('coalesce(sum(amount), 0)')->whereColumn('campaign_id', 'mkt_campaigns.id'), 'production');
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        return view('back.marketing.reports', ['campaigns' => $query->orderByDesc('id')->paginate(20)->withQueryString(), 'clients' => DB::table('mkt_clients')->pluck('name', 'id')]);
    }

    public function calendar(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $month = \Carbon\Carbon::createFromFormat('!Y-m', $request->input('month', now()->format('Y-m')));
        $contents = DB::table('mkt_contents')->join('mkt_campaigns', 'mkt_campaigns.id', '=', 'campaign_id')->join('mkt_channels', 'mkt_channels.id', '=', 'channel_id')
            ->select('mkt_contents.*', 'mkt_campaigns.name as campaign_name', 'mkt_channels.name as channel_name')
            ->whereBetween('mkt_contents.due_date', [$month->copy()->startOfMonth()->format('Y-m-d'), $month->copy()->endOfMonth()->format('Y-m-d')])
            ->orderBy('mkt_contents.due_date')->orderBy('mkt_contents.id')->get()->groupBy('due_date');

        return view('back.marketing.calendar', compact('contents', 'month'));
    }

    public function pipeline(Request $request)
    {
        $request->validate(['campaign_id' => 'nullable|integer|exists:mkt_campaigns,id']);
        $columns = [];
        foreach (config('marketing.modules.leads.fields.status.options') as $stage => $label) {
            $query = DB::table('mkt_leads')->where('status', $stage);
            if ($request->filled('campaign_id')) {
                $query->where('campaign_id', $request->integer('campaign_id'));
            }
            $columns[] = ['stage' => $stage, 'label' => $label, 'count' => (clone $query)->count(), 'value' => (clone $query)->sum('estimated_value'),
                'records' => $query->orderByRaw('due_date is null')->orderBy('due_date')->orderByDesc('id')->limit(20)->get()];
        }

        return view('back.marketing.pipeline', ['columns' => $columns, 'campaigns' => DB::table('mkt_campaigns')->pluck('name', 'id')]);
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
        }, 'marketing-'.$module.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
