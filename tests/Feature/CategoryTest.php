<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DefaultRole;
use App\Enums\Permission;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for the tests
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * A basic feature test example.
     */
    public function test_a_category_can_be_added(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(DefaultRole::ADMIN);
        $admin->givePermissionTo(Permission::CATEGORY_VIEW);
        $admin->givePermissionTo(Permission::CATEGORY_CREATE);

        $payload = [
            'name' => ['fr' => 'Justice', 'en' => 'Justice'],
            'slug' => 'justice',
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson(
            '/api/admin/categories',
            $payload,
            ['Accept-Language' => 'fr'],
        );

        $response->assertCreated()->assertJsonPath('data.name', ['fr' => 'Justice', 'en' => 'Justice']);

        $this->assertDatabaseHas('categories', [
            'slug' => 'justice',
        ]);
    }
}
