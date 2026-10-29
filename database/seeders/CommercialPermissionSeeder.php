<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CommercialPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $roles = [
            'commercial-responsable' => ['sales.manage', 'field.manage', 'field.report', 'export', 'audit'],
            'commercial-agent' => ['sales.agent', 'field.report'],
            'commercial-veille' => ['field.report'],
            'commercial-innovation' => ['innovation'],
            'commercial-ingenierie' => ['engineering'],
            'commercial-technique' => ['solution'],
            'commercial-juridique' => ['legal'],
            'commercial-finance' => ['finance'],
            'commercial-rh' => ['hr'],
        ];
        foreach (array_unique(array_merge(...array_values($roles))) as $permission) Permission::findOrCreate('com.'.$permission, 'web');
        foreach ($roles as $name => $permissions) Role::findOrCreate($name, 'web')->syncPermissions(array_map(fn ($p) => 'com.'.$p, $permissions));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
