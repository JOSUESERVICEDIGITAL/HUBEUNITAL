<?php

namespace App\Services;

use App\Models\CommercialAccessAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommercialAuthorization
{
    private array $accounts = [];
    public function refresh(): void { $this->accounts = []; }

    public function account(User $user): ?CommercialAccessAccount
    {
        if (!array_key_exists($user->id, $this->accounts)) {
            $account = CommercialAccessAccount::with(['roles.permissions', 'permissions'])->where('user_id', $user->id)->where('is_active', true)->first();
            $this->accounts[$user->id] = $account && DB::table('rh_employees')->where('id', $account->employee_id)->where('user_id', $user->id)->whereIn('status', ['active', 'onboarding'])->exists() ? $account : null;
        }
        return $this->accounts[$user->id];
    }

    public function can(User $user, string $permission): bool
    {
        return $user->role === 'superadmin' || ($this->account($user)?->checkPermissionTo('com.'.$permission, 'web') ?? false);
    }

    public function employeeId(User $user): ?int { return $this->account($user)?->employee_id; }

    public function chamber(User $user): string
    {
        if ($user->role === 'superadmin') return 'commerciale';
        foreach (['innovation' => 'innovation', 'engineering' => 'ingenierie', 'solution' => 'developpement', 'legal' => 'juridique', 'finance' => 'finance', 'hr' => 'rh'] as $permission => $chamber) {
            if ($this->can($user, $permission)) {
                if ($permission === 'solution') {
                    return DB::table('rh_employees')->join('rh_departments', 'rh_departments.id', '=', 'rh_employees.department_id')->join('chambers', 'chambers.id', '=', 'rh_departments.chamber_id')->where('rh_employees.id', $this->employeeId($user))->value('chambers.slug') ?? 'developpement';
                }
                return $chamber;
            }
        }
        return 'commerciale';
    }

    public function scope(User $user, string $module): ?string
    {
        $d = config('commercial.modules.'.$module);
        if (!$d) return null;
        if ($user->role === 'superadmin') return 'all';
        if ($module === 'visits' && $this->can($user, 'field.report')) return $this->can($user, 'sales.manage') ? 'all' : 'own';
        if ($d['group'] === 'field') {
            if ($this->can($user, 'field.manage')) return 'all';
            if ($this->can($user, 'innovation') || $this->can($user, 'engineering') || $this->can($user, 'solution')) return 'shared';
            if ($this->can($user, 'field.report')) return 'own';
            return null;
        }
        if ($module === 'contracts' && $this->can($user, 'legal')) return 'review';
        if ($module === 'invoices' && $this->can($user, 'finance')) return 'all';
        if ($module === 'commissions' && ($this->can($user, 'hr') || $this->can($user, 'finance'))) return 'all';
        if ($this->can($user, 'sales.manage')) return 'all';
        if ($this->can($user, 'sales.agent')) return $d['owner'] ? 'own' : 'all';
        return null;
    }

    public function mayWrite(User $user, string $module): bool
    {
        if (!$this->scope($user, $module)) return false;
        if ($user->role === 'superadmin') return true;
        if ($module === 'invoices') return $this->can($user, 'finance');
        if ($module === 'commissions') return $this->can($user, 'sales.manage');
        if ($module === 'visits' && $this->can($user, 'field.report')) return true;
        if (in_array($module, ['targets', 'catalog', 'portfolios'])) return $this->can($user, 'sales.manage');
        if ($module === 'clusters') return $this->can($user, 'innovation');
        if ($module === 'proposals') return $this->can($user, 'engineering');
        if ($module === 'solutions') return $this->can($user, 'solution') || $this->can($user, 'engineering');
        if (in_array($module, ['problems', 'observations', 'surveys'])) return $this->can($user, 'field.report') || $this->can($user, 'field.manage');
        return $this->can($user, 'sales.manage') || $this->can($user, 'sales.agent');
    }

    public function visibleModules(User $user): array
    {
        return array_filter(config('commercial.modules'), fn ($d, $module) => $this->scope($user, $module) !== null, ARRAY_FILTER_USE_BOTH);
    }
}
