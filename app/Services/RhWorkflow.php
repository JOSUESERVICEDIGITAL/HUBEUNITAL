<?php

namespace App\Services;

use Carbon\CarbonImmutable as Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RhWorkflow
{
    public function validate(Request $request, string $module, ?object $before, string $scope): array
    {
        $rules = [];
        $fields = config('rh.modules.'.$module.'.fields');
        foreach ($fields as $key => $field) {
            $rules[$key] = [$field['required'] ? 'required' : 'nullable', ...match ($field['type']) {
                'relation' => ['integer', Rule::exists($field['table'], 'id')],
                'select' => [Rule::in(array_keys($field['options']))],
                'date' => ['date_format:Y-m-d'],
                'datetime-local' => ['date_format:Y-m-d\TH:i'],
                'number' => ['integer', 'min:0', 'max:100000'],
                'decimal' => ['numeric', 'min:0', 'max:999999999999'],
                'file' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
                'email' => ['email', 'max:255'],
                'textarea' => ['string', 'max:20000'],
                default => ['string', 'max:255'],
            }];
        }
        if ($module === 'employees') {
            $rules['registration'][] = Rule::unique('rh_employees')->ignore($before?->id);
            $rules['user_id'][] = Rule::unique('rh_employees')->ignore($before?->id);
        }
        if ($module === 'attendance') $rules['schedule_id'][] = Rule::unique('rh_attendance')->ignore($before?->id);
        if ($module === 'balances') {
            $rules['year'] = ['required', 'integer', 'between:2000,2200'];
            $rules['type'][] = Rule::unique('rh_balances')->where(fn ($q) => $q->where('employee_id', $request->input('employee_id'))->where('year', $request->input('year')))->ignore($before?->id);
            $rules['entitlement'][] = 'max:366';
        }
        if ($module === 'evaluations') $rules['score'][] = 'max:100';
        if ($module === 'payroll') $rules['period'][] = 'date_format:Y-m';
        foreach ([['starts_on', 'ends_on'], ['starts_at', 'ends_at'], ['arrived_at', 'left_at']] as [$start, $end]) {
            if (isset($fields[$start], $fields[$end])) $rules[$end][] = 'after_or_equal:'.$start;
        }
        $data = $request->validate($rules);
        foreach ($fields as $key => $field) {
            if ($field['type'] !== 'file') $data[$key] ??= null;
        }
        if ($scope === 'self') {
            $employeeId = app(RhAuthorization::class)->employeeId($request->user());
            abort_unless(($data['employee_id'] ?? null) == $employeeId, 403);
            abort_unless($module === 'leaves' && $data['status'] === 'pending' && (!$before || $before->status === 'pending'), 403, 'Seules vos demandes en attente peuvent être modifiées.');
        }
        if (isset($data['employee_id'])) {
            $employee = DB::table('rh_employees')->find($data['employee_id']);
            if ($module === 'sales' && $employee->profession !== 'sales') $this->fail('employee_id', 'Sélectionnez un employé commercial.');
            if (!$before && in_array($employee->status, ['departed', 'suspended']) && in_array($module, ['attendance', 'leaves', 'schedules', 'assignments', 'careers'])) $this->fail('employee_id', 'Cet employé n’est pas actif.');
            if ($module === 'departures' && $data['date'] < $employee->hired_on) $this->fail('date', 'Le départ ne peut pas précéder l’entrée du collaborateur.');
        }
        foreach ($fields as $key => $field) {
            if ($field['type'] === 'datetime-local' && $data[$key]) $data[$key] = str_replace('T', ' ', $data[$key]).':00';
        }
        if ($module === 'contracts' && in_array($data['type'], ['fixed', 'renewal']) && !$data['ends_on']) $this->fail('ends_on', 'Précisez la date de fin ou de renouvellement.');
        if ($module === 'employees') {
            if ($data['status'] === 'departed' && (!$before || $before->status !== 'departed')) $this->fail('status', 'Enregistrez le départ dans Départs et fins de contrat pour conserver sa traçabilité.');
            if ($before && $before->status === 'departed') $this->fail('status', 'Le dossier est archivé après le départ.');
        }
        if ($module === 'applications') {
            if ($data['status'] === 'hired' && (!$before || !$before->employee_id)) $this->fail('status', 'Utilisez « Intégrer cette recrue » pour créer son dossier employé.');
            if ($before?->employee_id && $data['status'] !== 'hired') $this->fail('status', 'Cette candidature a déjà été intégrée.');
        }
        if ($module === 'payroll') {
            if ($before?->status === 'transmitted') $this->fail('status', 'Les éléments transmis à Finance sont verrouillés.');
            if ($data['status'] === 'transmitted') $this->fail('status', 'Utilisez la transmission à Finance après validation.');
        }
        if ($module === 'schedules') {
            $duration = $this->minutes($data['starts_at'], $data['ends_at']);
            if ($duration <= $data['break_minutes'] || $duration > 1440) $this->fail('ends_at', 'La vacation doit durer entre 1 minute et 24 heures, hors pause.');
            $overlap = DB::table('rh_schedules')->where('employee_id', $data['employee_id'])->where('starts_at', '<', $data['ends_at'])->where('ends_at', '>', $data['starts_at']);
            if ($before) $overlap->where('id', '!=', $before->id);
            if ($overlap->exists()) $this->fail('starts_at', 'Un autre horaire couvre déjà cette période.');
            if ($before && DB::table('rh_attendance')->where('schedule_id', $before->id)->exists()) $this->fail('starts_at', 'Cet horaire est déjà utilisé par un pointage.');
        }
        if ($module === 'attendance') $data = $this->attendance($data);
        if ($module === 'balances' && $before) {
            if ($before->employee_id != $data['employee_id'] || $before->year != $data['year'] || $before->type !== $data['type']) $this->fail('employee_id', 'L’employé, l’année et le type d’un solde existant sont immuables.');
            if ($data['entitlement'] < $before->used) $this->fail('entitlement', 'Les droits ne peuvent pas être inférieurs aux jours déjà consommés.');
        }
        if ($module === 'leaves') $this->leave($request, $data, $before);
        if (in_array($module, ['assignments', 'careers', 'departures']) && $before?->status === 'done') $this->fail('status', 'Cette opération est terminée et reste conservée dans l’historique.');
        if (in_array($module, ['assignments', 'careers']) && $data['status'] === 'done') {
            $effective = $data['date'] ?? $data['starts_on'];
            if ($effective > today(config('rh.timezone'))->toDateString()) $this->fail('status', 'La date d’effet n’est pas encore atteinte.');
        }
        if ($module === 'departures' && $data['status'] === 'done' && $data['date'] > today(config('rh.timezone'))->toDateString()) $this->fail('date', 'Le départ peut être clôturé à sa date d’effet.');
        return $data;
    }

    private function attendance(array $data): array
    {
        $schedule = DB::table('rh_schedules')->find($data['schedule_id']);
        if ($schedule->employee_id != $data['employee_id']) $this->fail('schedule_id', 'L’horaire appartient à un autre employé.');
        if (substr($data['arrived_at'], 0, 10) !== substr($schedule->starts_at, 0, 10)) $this->fail('arrived_at', 'L’arrivée doit correspondre au jour de début de la vacation.');
        $data['late_minutes'] = max(0, $this->minutes($schedule->starts_at, $data['arrived_at']));
        $data['worked_minutes'] = null;
        $data['overtime_minutes'] = null;
        if ($data['left_at']) {
            $span = $this->minutes($data['arrived_at'], $data['left_at']);
            if ($span > 1440 || $data['break_minutes'] > $span) $this->fail('left_at', 'Vérifiez la durée du pointage et la pause.');
            $data['worked_minutes'] = $span - $data['break_minutes'];
            $expected = $this->minutes($schedule->starts_at, $schedule->ends_at) - $schedule->break_minutes;
            $data['overtime_minutes'] = max(0, $data['worked_minutes'] - $expected);
        }
        return $data;
    }

    private function leave(Request $request, array &$data, ?object $before): void
    {
        $start = Date::parse($data['starts_on'], config('rh.timezone'));
        $end = Date::parse($data['ends_on'], config('rh.timezone'));
        if ($start->year !== $end->year) $this->fail('ends_on', 'Séparez les demandes couvrant deux années.');
        if ($data['days'] <= 0 || $data['days'] > $start->diffInDays($end) + 1 || fmod((float) $data['days'], 0.5) !== 0.0) $this->fail('days', 'Indiquez un nombre de jours positif par demi-journée, dans la période demandée.');
        if ($before && in_array($before->status, ['rejected', 'cancelled'])) $this->fail('status', 'Cette demande est clôturée. Créez une nouvelle demande.');
        if ($before?->status === 'approved') {
            foreach (['employee_id', 'type', 'starts_on', 'ends_on', 'days'] as $key) {
                if ($before->$key != $data[$key]) $this->fail($key, 'Annulez le congé approuvé avant d’en modifier la période ou le décompte.');
            }
            if (!in_array($data['status'], ['approved', 'cancelled'])) $this->fail('status', 'Un congé approuvé peut uniquement être annulé.');
        }
        $employee = DB::table('rh_employees')->find($data['employee_id']);
        if ($data['status'] !== 'pending' && $employee->user_id === $request->user()->id) $this->fail('status', 'La décision sur votre congé doit être prise par un autre responsable habilité.');
        $approvedBefore = $before?->status === 'approved';
        $approve = $data['status'] === 'approved';
        $commercialAgent = \App\Models\CommercialAccessAccount::where('employee_id', $data['employee_id'])->where('is_active', true)->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['commercial-agent', 'commercial-veille']))->exists();
        if ($approve && !$approvedBefore && $commercialAgent && (!$before || $before->commercial_opinion === 'pending')) $this->fail('status', 'L’avis du responsable commercial est nécessaire avant la décision RH.');
        if ($before?->status === 'pending') {
            foreach (['employee_id', 'type', 'starts_on', 'ends_on', 'days'] as $key) {
                if ($before->$key != $data[$key]) {
                    if ($approve && $commercialAgent) $this->fail($key, 'Enregistrez la modification en attente pour recueillir un nouvel avis commercial.');
                    $data['commercial_opinion'] = 'pending'; $data['commercial_notes'] = null; $data['commercial_reviewed_by'] = null;
                    break;
                }
            }
        }
        if ($approve) {
            $overlap = DB::table('rh_leaves')->where('employee_id', $data['employee_id'])->where('status', 'approved')->where('starts_on', '<=', $data['ends_on'])->where('ends_on', '>=', $data['starts_on']);
            if ($before) $overlap->where('id', '!=', $before->id);
            if ($overlap->exists()) $this->fail('starts_on', 'Un congé approuvé couvre déjà cette période.');
        }
        if ($approve !== $approvedBefore) {
            $balance = DB::table('rh_balances')->where('employee_id', $data['employee_id'])->where('year', $start->year)->where('type', $data['type'])->lockForUpdate()->first();
            if (!$balance) $this->fail('days', 'Configurez d’abord le solde de ce type de congé pour cette année.');
            $used = (float) $balance->used + ($approve ? (float) $data['days'] : -(float) $data['days']);
            if ($used > (float) $balance->entitlement || $used < 0) $this->fail('days', 'Le solde disponible est insuffisant ou incohérent.');
            DB::table('rh_balances')->where('id', $balance->id)->update(['used' => $used, 'updated_at' => now()]);
            $this->audit($request, 'balances', $balance->id, 'leave_balance', $balance, ['used' => $used]);
        }
        if ($data['status'] !== ($before?->status ?? 'pending')) $data['decided_by'] = $request->user()->id;
    }

    public function afterSave(Request $request, string $module, int $id, array $data): void
    {
        if (in_array($module, ['assignments', 'careers']) && $data['status'] === 'done') {
            $before = DB::table('rh_employees')->find($data['employee_id']);
            $change = ['department_id' => $data['department_id'], 'position' => $data['position']];
            DB::table('rh_employees')->where('id', $before->id)->update($change + ['updated_at' => now()]);
            $this->audit($request, 'employees', $before->id, 'assignment', $before, $change);
        }
        if ($module === 'departures' && $data['status'] === 'done') {
            $employee = DB::table('rh_employees')->find($data['employee_id']);
            DB::table('rh_employees')->where('id', $employee->id)->update(['status' => 'departed', 'updated_at' => now()]);
            $this->audit($request, 'employees', $employee->id, 'departure', $employee, ['status' => 'departed']);
            foreach (DB::table('rh_contracts')->where('employee_id', $employee->id)->where('status', 'active')->get() as $contract) {
                $change = ['status' => 'ended'];
                if ($contract->starts_on <= $data['date'] && (!$contract->ends_on || $contract->ends_on > $data['date'])) $change['ends_on'] = $data['date'];
                DB::table('rh_contracts')->where('id', $contract->id)->update($change + ['updated_at' => now()]);
                $this->audit($request, 'contracts', $contract->id, 'departure', $contract, $change);
            }
            if ($employee->user_id) {
                $account = DB::table('rh_access_accounts')->where('user_id', $employee->user_id)->first();
                DB::table('rh_access_accounts')->where('user_id', $employee->user_id)->update(['is_active' => false, 'updated_at' => now()]);
                if ($account) $this->audit($request, 'access', $account->id, 'revoke', ['is_active' => $account->is_active], ['is_active' => false]);
            }
        }
    }

    public function audit(Request $request, string $module, int $id, string $action, $before, $after): void
    {
        DB::table('chamber_audits')->insert([
            'chamber_slug' => 'rh', 'user_id' => $request->user()->id, 'module' => $module, 'record_id' => $id, 'action' => $action,
            'before' => $before ? json_encode($before) : null, 'after' => $after ? json_encode($after) : null, 'created_at' => now(),
        ]);
    }

    private function minutes(string $start, string $end): int
    {
        return (int) Date::parse($start, config('rh.timezone'))->diffInMinutes(Date::parse($end, config('rh.timezone')), false);
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
