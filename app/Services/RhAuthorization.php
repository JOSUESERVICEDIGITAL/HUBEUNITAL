<?php

namespace App\Services;

use App\Models\RhAccessAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RhAuthorization
{
    private array $accounts = [];
    private array $employees = [];

    public function refresh(): void
    {
        $this->accounts = [];
        $this->employees = [];
    }

    public function account(User $user): ?RhAccessAccount
    {
        if (!array_key_exists($user->id, $this->accounts)) {
            $this->accounts[$user->id] = RhAccessAccount::with(['roles.permissions', 'permissions'])->where('user_id', $user->id)->where('is_active', true)->first();
        }
        return $this->accounts[$user->id];
    }

    public function can(User $user, string $permission): bool
    {
        return $user->role === 'superadmin' || ($this->account($user)?->checkPermissionTo($permission, 'web') ?? false);
    }

    public function employeeId(User $user): ?int
    {
        if (!array_key_exists($user->id, $this->employees)) {
            $this->employees[$user->id] = DB::table('rh_employees')->where('user_id', $user->id)->whereIn('status', ['active', 'onboarding'])->value('id');
        }
        return $this->employees[$user->id];
    }

    public function scope(User $user, string $module): ?string
    {
        $definition = config('rh.modules.'.$module);
        if (!$definition) return null;
        if ($this->can($user, 'rh.'.$definition['area'].'.view')) return 'all';
        if ($definition['self'] && $this->can($user, 'rh.self') && $this->employeeId($user)) return 'self';
        return null;
    }

    public function mayWrite(User $user, string $module): bool
    {
        $definition = config('rh.modules.'.$module);
        return $definition && ($this->can($user, 'rh.'.$definition['area'].'.manage') || ($module === 'leaves' && $this->scope($user, $module) === 'self'));
    }

    public function visibleModules(User $user): array
    {
        return array_filter(config('rh.modules'), fn ($definition, $module) => $this->scope($user, $module) !== null, ARRAY_FILTER_USE_BOTH);
    }
}
