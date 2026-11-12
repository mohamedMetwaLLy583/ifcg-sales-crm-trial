<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrderPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(['view_order', 'edit_order', 'manage_order'])
            ->mapWithKeys(fn (string $permission) => [
                $permission => Permission::findOrCreate($permission, 'web'),
            ]);

        Role::findOrCreate('admin', 'web')->givePermissionTo($permissions->values()->all());

        Role::findOrCreate('branch_manager', 'web')->givePermissionTo([
            $permissions['view_order'],
            $permissions['edit_order'],
        ]);

        Role::findOrCreate('branch_staff', 'web')->givePermissionTo([
            $permissions['view_order'],
        ]);
    }
}
