<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/auth/me');

        $response
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id, 'email' => $user->email]]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson(
            '/api/auth/update-password',
            [
                'current_password' => 'old_password',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ],
        );

        $response->assertStatus(200);
        $this->assertTrue(
            Hash::check(
                'new_password',
                $user->fresh()->password,
            ),
        );
    }

    public function test_validation_errors_are_localized(): void
    {
        $user = User::factory()->create([
            'preferred_language' => 'fr',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson(
            '/api/auth/update-password',
            [
                'current_password' => 'wrong',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ],
        );

        $response->assertStatus(422);
        $this->assertEquals('fr', App::getLocale());
    }
}
