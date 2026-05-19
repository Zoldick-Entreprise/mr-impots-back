<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\DownloadResource;
use App\Http\Resources\UserResource;
use App\Models\Download;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ProfileController extends Controller
{
    /**
     * Log out the authenticated user.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return JsonResponse The logout message.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }

    /**
     * Display the authenticated user's profile.
     *
     * @return JsonResponse The user's profile data.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  UpdateProfileRequest  $request  The validated profile update request.
     * @return JsonResponse The updated user profile data.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update($request->only('name', 'preferred_language'));

        if ($request->has('preferred_language')) {
            app()->setLocale($request->input('preferred_language'));
        }

        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return response()->json([
            'data' => UserResource::make($user->refresh()),
        ]);
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  UpdatePasswordRequest  $request  The validated password update request.
     * @return JsonResponse Success message.
     *
     * @throws ValidationException If the user registered via Google.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->password === null && $user->google_id !== null) {
            throw ValidationException::withMessages([
                'password' => [__('auth.google_password_change_blocked')],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->json([
            'message' => __('auth.password_updated'),
        ]);
    }

    public function downloads(Request $request): JsonResponse
    {
        $downloads = Download::where('user_id', auth()->id())
            ->latest()
            ->paginate();

        return response()->json([
            'data' => DownloadResource::collection($downloads),
        ]);
    }
}
