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

        // Create 'user' role and assign permissions
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions([
            PermissionEnum::DOCUMENT_READ->value,
            PermissionEnum::DOCUMENT_DOWNLOAD->value,
            PermissionEnum::FAVORITE_ALL->value,
            PermissionEnum::SEARCH_ALL->value,
        ]);

        // Create 'admin' role and assign default admin permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            PermissionEnum::ADMIN_ACCESS->value,
            PermissionEnum::USER_VIEW->value,
            PermissionEnum::DOCUMENT_ALL->value,
            PermissionEnum::CATEGORY_ALL->value,
            PermissionEnum::VIDEO_ALL->value,
        ]);

        // Create 'super-admin' role
        // Note: The 'super-admin' gets all permissions implicitly via Gate::before in AppServiceProvider
        $superAdminRole = Role::firstOrCreate(['name' => DefaultRole::SUPER_ADMIN]);

        // Create default super-admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(
                    'password',
                ),
            ],
        );

        if (! $superAdmin->hasRole(DefaultRole::SUPER_ADMIN)) {
            $superAdmin->assignRole($superAdminRole);
        }
    }
}
