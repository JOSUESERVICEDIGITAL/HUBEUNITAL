<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RhPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = ['rh.self', 'rh.audit', 'rh.reports', 'rh.payroll.transfer'];
        foreach (['personnel', 'time', 'recruitment', 'career', 'payroll', 'discipline'] as $area) {
            foreach (['view', 'manage', 'export'] as $action) $permissions[] = 'rh.'.$area.'.'.$action;
        }
        foreach ($permissions as $name) Permission::findOrCreate($name, 'web');
        $roles = [
            'rh-responsable' => $permissions,
            'rh-assistant' => array_values(array_filter($permissions, fn ($name) => preg_match('/^rh\.(personnel|time|recruitment|career)\./', $name) || $name === 'rh.reports')),
            'rh-finance' => ['rh.payroll.view', 'rh.payroll.export', 'rh.payroll.transfer'],
            'rh-employe' => ['rh.self'],
        ];
        foreach ($roles as $name => $grants) Role::findOrCreate($name, 'web')->syncPermissions($grants);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
