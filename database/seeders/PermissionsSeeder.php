<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        foreach (RolesEnum::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        // Crear permisos
        foreach (PermissionsEnum::cases() as $permission) {
            Permission::create(['name' => $permission->value]);
        }

        // Asignar permisos al rol de Admin
        $adminRole = Role::findByName(RolesEnum::ADMIN->value);
        $adminRole->givePermissionTo(PermissionsEnum::VIEW_USERS->value);
        $adminRole->givePermissionTo(PermissionsEnum::CREATE_USERS->value);
        $adminRole->givePermissionTo(PermissionsEnum::EDIT_USERS->value);
        $adminRole->givePermissionTo(PermissionsEnum::DELETE_USERS->value);
    }
}
