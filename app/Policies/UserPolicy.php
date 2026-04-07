<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

/**
 * Class UserPolicy
 *
 * Manages authorization rules for User-related actions.
 * Note: The 'super-admin' role implicitly passes all checks via the Gate::before
 * definition in the AppServiceProvider.
 */
final class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user  The authenticated user making the request.
     * @return bool True if the user has permission to view the users list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::USER_VIEW->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user  The authenticated user making the request.
     * @param  User  $model  The user model being viewed.
     * @return bool True if the user has permission to view specific users.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::USER_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user  The authenticated user making the request.
     * @return bool True if the user has permission to create new users.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::USER_CREATE->value);
    }

    /**
     * Determine whether the user can create an administrator.
     *
     * @param  User  $user  The authenticated user making the request.
     * @return bool True if the user has permission to create administrators.
     */
    public function createAdmin(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ADMIN_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user  The authenticated user making the request.
     * @param  User  $model  The user model being updated.
     * @return bool True if the user has permission to update users.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::USER_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user  The authenticated user making the request.
     * @param  User  $model  The user model being deleted.
     * @return bool True if the user has permission to delete users.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::USER_DELETE->value);
    }

    /**
     * Determine whether the user can assign roles to another user.
     *
     * @param  User  $user  The authenticated user making the request.
     * @param  User  $model  The user model receiving the role.
     * @return bool True if the user has permission to assign roles.
     */
    public function assignRole(User $user, User $model): bool
    {
        return $user->hasPermissionTo(Permission::ROLE_ASSIGN->value);
    }
}
