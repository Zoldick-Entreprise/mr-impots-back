<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

/**
 * Class GoogleOAuthTest
 *
 * Feature tests for the Google OAuth2 authentication flow.
 * Covers redirection, callback handling, account creation, account linking,
 * and specific restrictions (e.g., blocking password resets for OAuth-only accounts).
 */
final class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the redirect route returns a valid Google OAuth URL.
     */
    public function test_redirect_returns_google_oauth_url(): void
    {
        $response = $this->getJson('/api/auth/google/redirect');

        $response->assertStatus(200)->assertJsonStructure(['url']);

        $this->assertStringContainsString(
            'accounts.google.com/o/oauth2/auth',
            $response->json('url'),
        );
    }

    /**
     * Test that a new user is created when authenticating with a previously unknown Google account.
     */
    public function test_callback_creates_new_user_if_not_exists(): void
    {
        $this->mockSocialite(
            '12345',
            'newuser@example.com',
            'New User',
            'http://example.com/avatar.jpg',
        );

        $response = $this->getJson('/api/auth/google/callback');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'avatar'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'google_id' => '12345',
            'avatar' => 'http://example.com/avatar.jpg',
            'password' => null,
        ]);
    }

    /**
     * Test that an existing standard account (email/password) is linked to a Google account
     * upon first OAuth login, and the avatar is updated if previously empty.
     */
    public function test_callback_links_existing_user_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'google_id' => null,
            'avatar' => null,
        ]);

        $this->mockSocialite(
            '67890',
            'existing@example.com',
            'Existing User',
            'http://example.com/new-avatar.jpg',
        );

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'existing@example.com',
            'google_id' => '67890',
            'avatar' => 'http://example.com/new-avatar.jpg',
        ]);

        $this->assertNotNull(
            $user->fresh()->password,
            'Existing password should not be erased',
        );
    }

    /**
     * Test that an existing Google-linked user can log in successfully.
     */
    public function test_callback_logs_in_existing_google_user(): void
    {
        $user = User::factory()->create([
            'email' => 'googleuser@example.com',
            'google_id' => '99999',
            'password' => null,
        ]);

        $this->mockSocialite(
            '99999',
            'googleuser@example.com',
            'Google User',
            'http://example.com/avatar.jpg',
        );

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertStatus(200);
        $this->assertEquals($user->id, $response->json('data.user.id'));
    }

    /**
     * Test that users created exclusively via Google OAuth cannot request a password reset.
     */
    public function test_forgot_password_is_blocked_for_google_only_users(): void
    {
        User::factory()->create([
            'email' => 'oauthonly@example.com',
            'google_id' => 'oauth-123',
            'password' => null,
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'oauthonly@example.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);

        $this->assertStringContainsString(
            'authenticated via Google',
            $response->json('errors.email.0'),
        );
    }

    /**
     * Mock the Socialite facade to simulate a Google OAuth callback.
     *
     * @param  string  $id  The mock Google user ID.
     * @param  string  $email  The mock Google user email.
     * @param  string  $name  The mock Google user name.
     * @param  string  $avatar  The mock Google user avatar URL.
     */
    private function mockSocialite(
        string $id,
        string $email,
        string $name,
        string $avatar,
    ): void {
        $abstractUser = Mockery::mock("Laravel\Socialite\Two\User");
        $abstractUser->shouldReceive('getId')->andReturn($id);
        $abstractUser->shouldReceive('getEmail')->andReturn($email);
        $abstractUser->shouldReceive('getName')->andReturn($name);
        $abstractUser->shouldReceive('getAvatar')->andReturn($avatar);

        $provider = Mockery::mock("Laravel\Socialite\Contracts\Provider");
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);
    }
}
