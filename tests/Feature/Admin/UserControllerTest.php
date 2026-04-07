<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\DefaultRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for the tests
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole(DefaultRole::USER);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/admins')
            ->assertForbidden();
    }

    public function test_admin_can_list_normal_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(DefaultRole::ADMIN);

        $normalUser = User::factory()->create();
        $normalUser->assignRole('user');

        $response = $this->actingAs($admin, 'sanctum')->getJson(
            '/api/admin/users',
        );

        $response
            ->assertOk()
            ->assertJsonFragment(['email' => $normalUser->email]);
    }

    public function test_admin_can_list_admins_but_not_super_admins(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(DefaultRole::ADMIN);

        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole(DefaultRole::ADMIN);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(DefaultRole::SUPER_ADMIN);

        $response = $this->actingAs($admin, 'sanctum')->getJson(
            '/api/admin/admins',
        );

        $response
            ->assertOk()
            ->assertJsonFragment(['email' => $otherAdmin->email])
            ->assertJsonMissing(['email' => $superAdmin->email]);
    }

    public function test_super_admin_can_list_all_admins(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(DefaultRole::SUPER_ADMIN);

        $otherSuperAdmin = User::factory()->create();
        $otherSuperAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin, 'sanctum')->getJson(
            '/api/admin/admins',
        );

        $response
            ->assertOk()
            ->assertJsonFragment(['email' => $otherSuperAdmin->email]);
    }

    public function test_admin_without_create_permission_cannot_create_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(DefaultRole::ADMIN);

        $payload = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/admins', $payload)
            ->assertForbidden();
    }

    public function test_super_admin_can_create_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(DefaultRole::SUPER_ADMIN);

        $payload = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $response = $this->actingAs($superAdmin, 'sanctum')->postJson(
            '/api/admin/admins',
            $payload,
        );

        $response
            ->assertCreated()
            ->assertJsonPath('user.email', 'newadmin@example.com')
            ->assertJsonPath('user.roles.0.name', 'admin');

        $this->assertDatabaseHas('users', ['email' => 'newadmin@example.com']);
    }

    public function test_super_admin_can_create_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(DefaultRole::SUPER_ADMIN);

        $payload = [
            'name' => 'New Super Admin',
            'email' => 'newsuperadmin@example.com',
            'password' => 'password123',
            'role' => DefaultRole::SUPER_ADMIN,
        ];

        $response = $this->actingAs($superAdmin, 'sanctum')->postJson(
            '/api/admin/admins',
            $payload,
        );

        $response
            ->assertCreated()
            ->assertJsonPath('user.roles.0.name', 'super-admin');
    }

    public function test_admin_cannot_update_role_without_permission(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(DefaultRole::ADMIN);

        $user = User::factory()->create();
        $user->assignRole(DefaultRole::USER);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/role", [
                'role' => 'admin',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_user_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($superAdmin, 'sanctum')->patchJson(
            "/api/admin/users/{$user->id}/role",
            [
                'role' => DefaultRole::ADMIN,
            ],
        );

        $response->assertOk()->assertJsonPath('user.roles.0.name', 'admin');

        $this->assertTrue($user->fresh()->hasRole(DefaultRole::ADMIN));
    }
}
