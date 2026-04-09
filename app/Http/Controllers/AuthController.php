<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class AuthController
 *
 * Handles user authentication processes including registration, login,
 * password management, and OAuth2 social authentication via Google.
 */
final class AuthController extends Controller
{
    /**
     * Register a new user with standard credentials.
     *
     * @param  Request  $request  The incoming HTTP request containing registration data.
     * @return JsonResponse Contains the created user resource and authentication token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'preferred_language' => 'nullable|in:en,fr',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'preferred_language' => $validated['preferred_language'] ?? 'fr',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(
            [
                'user' => UserResource::make($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            201,
        );
    }

    /**
     * Authenticate a user with email and password.
     *
     * @param  Request  $request  The incoming HTTP request containing login credentials.
     * @return JsonResponse Contains the user resource and authentication token.
     *
     * @throws ValidationException If authentication fails.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (
            ! $user ||
            ! $user->password ||
            ! Hash::check($request->password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.login.success'),
            'data' => [
                'token' => $token,
                'user' => UserResource::make($user),
            ],
        ]);
    }

    /**
     * Send a password reset link to the given email address.
     *
     * Blocks requests for users created via Google OAuth who do not have a password.
     *
     * @param  Request  $request  The incoming HTTP request containing the email.
     * @return JsonResponse Status message of the reset link dispatch.
     *
     * @throws ValidationException If the email is invalid or the user cannot reset their password.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Block password reset for Google OAuth users who never set a password
        if ($user && $user->password === null && $user->google_id !== null) {
            throw ValidationException::withMessages([
                'email' => [__('auth.google_password_reset')],
            ]);
        }

        $status = Password::broker()->sendResetLink($request->only('email'));

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Reset the user's password using a token.
     *
     * @param  Request  $request  The incoming HTTP request containing reset data.
     * @return JsonResponse Status message of the password reset.
     *
     * @throws ValidationException If the reset token or data is invalid.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token',
            ),
            function ($user, $password) {
                $user
                    ->forceFill([
                        'password' => Hash::make($password),
                    ])
                    ->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            },
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  Request  $request  The incoming HTTP request containing old and new passwords.
     * @return JsonResponse Success message.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => __('auth.password_updated'),
        ]);
    }

    /**
     * Generate the Google OAuth redirect URL.
     *
     * Returns the authorization URL so the client (frontend) can redirect the user.
     *
     * @return JsonResponse The JSON payload containing the redirect URL.
     */
    public function redirectToGoogle(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * Handle the callback from Google OAuth.
     *
     * Links existing accounts, logs in known users, or creates new accounts for first-time visitors.
     *
     * @return JsonResponse Contains the user resource and authentication token.
     *
     * @throws ValidationException If OAuth authentication fails.
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'oauth' => [__('auth.google_failed')],
            ]);
        }

        // 1. Check if we already have this exact Google account linked
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            // 2. Fallback to checking by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Link Google to the existing account
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            } else {
                // 3. Create a brand new account without a password
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => null, // Password intentionally left null for OAuth users
                ]);

                if ($googleUser->getAvatar()) {
                    try {
                        $user
                            ->addMediaFromUrl($googleUser->getAvatar())
                            ->toMediaCollection('avatar');
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch Google avatar: '.$e->getMessage());
                    }
                }
            }
        }

        // Generate an API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.login.success'),
            'data' => [
                'token' => $token,
                'user' => UserResource::make($user),
            ],
        ]);
    }
}
