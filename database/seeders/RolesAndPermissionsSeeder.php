<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DefaultRole;
use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seed the roles and permissions for the application.
 *
 * This seeder defines the granular permissions for the application and assigns
 * them to the default roles: 'super-admin', 'admin', and 'user'.
 */
final class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method resets the cached permissions, creates all necessary permissions,
     * creates the predefined roles, and assigns the appropriate permissions to each role.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions from Enum
        foreach (PermissionEnum::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }

        Role::firstOrCreate(['name' => DefaultRole::USER]);

        $adminRole = Role::firstOrCreate(['name' => DefaultRole::ADMIN]);
        $adminRole->syncPermissions([
            PermissionEnum::ADMIN_ACCESS->value,
        ]);

        $superAdminRole = Role::firstOrCreate([
            'name' => DefaultRole::SUPER_ADMIN,
        ]);

        // Create default super-admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ],
        );

        if (! $superAdmin->hasRole(DefaultRole::SUPER_ADMIN)) {
            $superAdmin->assignRole($superAdminRole);
        }
    }
}
