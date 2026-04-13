<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\DefaultRole;
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
    public function test_an_category_can_be_added(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(DefaultRole::ADMIN);

        $payload = [
            'name' => ['fr' => 'Justice', 'en' => 'Justice'],
            'slug' => 'justice',
            'icon' => 'fa-balance-scale',
            'parent_id' => null,
            'sort_order' => 3,
        ];

        $response = $this->actingAs($admin, 'sanctum')->postJson(
            '/api/admin/categories', $payload
            );

        $response
            ->assertCreated()
            ->assertJsonPath('data.name.fr', 'Justice')
            ->assertJsonPath('data.name.en', 'Justice');

        $this->assertDatabaseHas('categories', [
            'slug' => 'justice',
        ]);
    }
}
