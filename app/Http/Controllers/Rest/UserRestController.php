<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rest;

use App\Enums\DefaultRole;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Class UserController
 *
 * Handles administrative actions related to users, such as
 * listing normal users, listing administrators, and managing assigned roles.
 */
final class UserRestController extends Controller
{
    /**
     * UserController constructor.
     *
     * @param  UserRepository  $userRepository  The repository handling User data retrieval.
     */
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Retrieve a list of normal users (non-admins).
     *
     * The results can be filtered, sorted, included, and paginated dynamically
     * using the query builder via HTTP request parameters.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return JsonResponse A JSON response containing the users collection or paginator.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $users = $this->userRepository->getNormalUsers($request->query());

        return AdminResource::collection($users)->response();
    }

    /**
     * Retrieve a list of administrator users.
     *
     * Super-admins are automatically hidden from the results unless the requesting
     * user is also a super-admin.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return JsonResponse A JSON response containing the admins collection or paginator.
     */
    public function admins(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $admins = $this->userRepository->getAdminUsers(
            $request->user(),
            $request->query(),
        );

        return AdminResource::collection($admins)->response();
    }

    public function show(Request $request, User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        $user = $this->userRepository->retrieve($user->id);

        return AdminResource::make($user)->response();
    }

    /**
     * Create a new administrator user.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return JsonResponse A JSON response with the newly created admin.
     */
    public function storeAdmin(Request $request): JsonResponse
    {
        Gate::authorize('createAdmin', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
            ],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,super-admin'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // Prevent creating 'super-admin' unless the current user is a super-admin
        if (
            $validated['role'] === DefaultRole::SUPER_ADMIN &&
            ! $request->user()->hasRole(DefaultRole::SUPER_ADMIN)
        ) {
            abort(403, 'Unauthorized to create a super-admin.');
        }

        /** @var User $admin */
        $admin = $this->userRepository->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $admin->assignRole($validated['role']);

        if (! empty($validated['permissions'])) {
            $admin->syncPermissions([...$validated['permissions'], Permission::ADMIN_ACCESS]);
        }

        return response()->json(
            [
                'message' => 'Administrator created successfully.',
                'user' => AdminResource::make(
                    $admin->load(['roles', 'permissions']),
                ),
            ],
            201,
        );
    }

    /**
     * Update the role of a specific user.
     *
     * This method syncs the provided role to the user, replacing any previously
     * assigned roles. Only authorized administrators can perform this action,
     * with strict checks preventing privilege escalation.
     *
     * @param  Request  $request  The incoming HTTP request containing the new role.
     * @param  User  $user  The user model being updated (resolved via route model binding).
     * @return JsonResponse A JSON response indicating the operation's success.
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        Gate::authorize('assignRole', $user);

        // Validate that the requested role is a string and exists in the roles table
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // Prevent assigning 'super-admin' unless the current user is a super-admin
        if (
            $validated['role'] === 'super-admin' &&
            ! $request->user()->hasRole('super-admin')
        ) {
            abort(403, 'Unauthorized to assign super-admin role.');
        }

        // Prevent non-super-admins from assigning the 'admin' role if needed
        if (
            $validated['role'] === DefaultRole::ADMIN->value &&
            ! $request->user()->hasRole(DefaultRole::SUPER_ADMIN) &&
            ! $request->user()->hasPermissionTo(Permission::ADMIN_CREATE)
        ) {
            abort(403, 'Unauthorized to assign admin role.');
        }

        // Sync the role using Spatie Laravel Permission's provided method
        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'User role updated successfully.',
            'user' => AdminResource::make($user->load('roles')),
        ]);
    }
}
