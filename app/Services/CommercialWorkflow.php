<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommercialWorkflow
{
    public const ARCHIVABLE = ['contacts', 'interactions', 'prospects', 'clients', 'campaigns', 'appointments', 'visits', 'followups', 'catalog', 'reports', 'targets', 'portfolios'];
    public function validate(Request $request, string $module, ?object $before): array
    {
        $fields = config('commercial.modules.'.$module.'.fields');
        $rules = [];
        foreach ($fields as $key => $f) {
            $rules[$key] = [$f['required'] ? 'required' : 'nullable', ...match ($f['type']) {
                'relation' => ['integer', Rule::exists($f['table'], 'id')],
                'select' => [Rule::in(array_keys($f['options']))],
                'date' => ['date_format:Y-m-d'],
                'datetime-local' => ['date_format:Y-m-d\TH:i'],
                'number' => ['integer', 'min:0', 'max:1000000'],
                'decimal' => ['numeric', 'min:'.(in_array($key, ['latitude', 'longitude']) ? '-180' : '0'), 'max:999999999999'],
                'email' => ['email', 'max:255'],
                'textarea' => ['string', 'max:20000'],
                default => ['string', 'max:255'],
            }];
        }
        if (isset($fields['period'])) $rules['period'][] = 'date_format:Y-m';
        if (isset($fields['ends_on'])) $rules['ends_on'][] = 'after_or_equal:starts_on';
        if ($module === 'invoices') { $rules['due_date'][] = 'after_or_equal:issued_on'; $rules['name'][] = Rule::unique('com_invoices')->ignore($before?->id); }
        if ($module === 'commissions') $rules['rate'][] = 'max:100';
        if ($module === 'problems') { $rules['latitude'][] = 'between:-90,90'; $rules['longitude'][] = 'between:-180,180'; }
        if ($module === 'orders') $rules['quote_id'][] = Rule::unique('com_orders')->ignore($before?->id);
        if (in_array($module, ['proposals', 'solutions'])) $rules['problem_id'][] = Rule::unique('com_'.$module)->ignore($before?->id);
        if ($module === 'commissions') $rules['order_id'][] = Rule::unique('com_commissions')->where('employee_id', $request->input('employee_id'))->ignore($before?->id);
        $data = $request->validate($rules);
        foreach ($fields as $key => $f) $data[$key] ??= null;
        $access = app(CommercialAuthorization::class);
        if ($access->scope($request->user(), $module) === 'own' && isset($fields['employee_id'])) abort_unless($data['employee_id'] == $access->employeeId($request->user()), 403);
        if (isset($data['employee_id'])) {
            $employee = DB::table('rh_employees')->find($data['employee_id']);
            if (!in_array($employee->status, ['active', 'onboarding'])) $this->fail('employee_id', 'Le collaborateur doit être actif dans la RH.');
            if ($before && $before->employee_id != $data['employee_id'] && !$access->can($request->user(), 'sales.manage') && !$access->can($request->user(), 'field.manage')) $this->fail('employee_id', 'Seul le responsable peut réaffecter un dossier.');
        }
        foreach ($fields as $key => $f) if ($f['type'] === 'datetime-local' && $data[$key]) $data[$key] = str_replace('T', ' ', $data[$key]).':00';
        foreach (['latitude', 'longitude'] as $coordinate) if (isset($data[$coordinate])) $data[$coordinate] = round((float) $data[$coordinate], 2);
        if ($module === 'problems' && (($data['latitude'] === null) !== ($data['longitude'] === null))) $this->fail('latitude', 'Renseignez les deux coordonnées approximatives, ou aucune.');
        foreach (['client_id' => 'com_clients', 'campaign_id' => 'com_campaigns', 'opportunity_id' => 'com_opportunities', 'quote_id' => 'com_quotes', 'order_id' => 'com_orders'] as $key => $table) {
            if (empty($data[$key]) || !isset($data['employee_id'])) continue;
            $related = DB::table($table)->find($data[$key]);
            if ($related->employee_id != $data['employee_id']) $this->fail($key, 'Ce dossier est affecté à un autre agent. Réaffectez les dossiers associés avant de poursuivre.');
            if (isset($data['client_id'], $related->client_id) && $related->client_id != $data['client_id']) $this->fail($key, 'Le client du dossier associé est différent.');
        }
        if ($module === 'prospects' && $data['status'] === 'converted' && !$before?->converted_client_id) $this->fail('status', 'Utilisez la conversion en client pour créer un dossier lié.');
        if ($module === 'prospects' && $before?->converted_client_id && $data['status'] !== 'converted') $this->fail('status', 'Ce prospect est déjà converti.');
        if ($module === 'quotes' && $before && DB::table('com_orders')->where('quote_id', $before->id)->exists()) $this->fail('status', 'Le devis est lié à une commande et ne peut plus être modifié.');
        if ($module === 'orders') {
            $quote = DB::table('com_quotes')->find($data['quote_id']);
            if ($quote->status !== 'accepted') $this->fail('quote_id', 'Le devis doit être accepté.');
            if ($quote->valid_until < $data['date']) $this->fail('date', 'Le devis a expiré à cette date.');
            $data['amount'] = $quote->amount;
            if ($before && (DB::table('com_invoices')->where('order_id', $before->id)->exists() || DB::table('com_commissions')->where('order_id', $before->id)->exists())) $this->fail('status', 'La commande est liée à Finance ou à une commission. Elle est verrouillée.');
        }
        if ($module === 'contracts') {
            if ($data['status'] === 'signed' && ($before?->legal_status !== 'approved' || $before->terms !== $data['terms'] || $before->order_id != $data['order_id'] || $before->starts_on !== $data['starts_on'] || $before->ends_on != $data['ends_on'])) $this->fail('status', 'Une validation Juridique des conditions actuelles est nécessaire avant signature.');
            if (!$before || $before->terms !== $data['terms'] || $before->order_id != $data['order_id'] || $before->starts_on !== $data['starts_on'] || $before->ends_on != $data['ends_on']) {
                $data['legal_status'] = 'pending'; $data['reviewed_by'] = null; $data['legal_notes'] = null;
            }
        }
        if ($module === 'invoices') {
            $order = DB::table('com_orders')->find($data['order_id']);
            if ($order->status !== 'confirmed') $this->fail('order_id', 'La commande n’est pas confirmée.');
            $paid = $before ? DB::table('com_payments')->where('invoice_id', $before->id)->sum('amount') : 0;
            if ($paid > 0 && ($data['status'] === 'cancelled' || $data['amount'] != $before->amount || $data['order_id'] != $before->order_id || $data['client_id'] != $before->client_id || $data['employee_id'] != $before->employee_id)) $this->fail('amount', 'Une facture encaissée est verrouillée sur son montant et ses liens.');
            $billed = DB::table('com_invoices')->where('order_id', $order->id)->where('status', 'issued');
            if ($before) $billed->where('id', '!=', $before->id);
            if ($data['status'] === 'issued' && (float) $billed->sum('amount') + (float) $data['amount'] > (float) $order->amount) $this->fail('amount', 'Le total facturé ne peut pas dépasser le montant de la commande.');
        }
        if ($module === 'commissions') {
            $order = DB::table('com_orders')->find($data['order_id']);
            if ($order->status !== 'confirmed') $this->fail('order_id', 'La vente doit être confirmée.');
            if ($before && $before->status !== 'proposed') $this->fail('status', 'Une commission en validation est verrouillée.');
            $data['amount'] = round((float) $order->amount * (float) $data['rate'] / 100, 2);
        }
        if (in_array($module, ['observations', 'surveys']) && $before && $before->problem_id != $data['problem_id']) $this->fail('problem_id', 'Un rapport conserve son problème d’origine.');
        if ($module === 'problems' && $before && $before->status !== 'reported') $this->fail('status', 'Le signalement est transmis. Complétez-le avec un rapport d’observation.');
        if ($module === 'proposals') {
            $problem = DB::table('com_problems')->find($data['problem_id']);
            if (!in_array($problem->status, ['study', 'proposed'])) $this->fail('problem_id', 'Le besoin doit être en étude avant de proposer un projet.');
            if ($before && $before->problem_id != $data['problem_id']) $this->fail('problem_id', 'Le projet conserve son besoin d’origine.');
            if ($problem->status === 'proposed' && $before) $this->fail('problem_id', 'Le projet transmis à la réalisation est verrouillé.');
        }
        if ($module === 'solutions') {
            $proposal = DB::table('com_proposals')->find($data['proposal_id']);
            $problem = DB::table('com_problems')->find($data['problem_id']);
            if ($proposal->problem_id != $problem->id || !in_array($problem->status, ['proposed', 'development', 'tested', 'deployed'])) $this->fail('proposal_id', 'La solution doit correspondre au projet proposé pour ce besoin.');
            if ($before && ($before->problem_id != $data['problem_id'] || $before->proposal_id != $data['proposal_id'])) $this->fail('problem_id', 'La solution conserve son origine.');
        }
        return $data;
    }

    public function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert(['chamber_slug' => 'commerciale', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action, 'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now()]);
    }

    public function fail(string $field, string $message): never { throw ValidationException::withMessages([$field => $message]); }
}
