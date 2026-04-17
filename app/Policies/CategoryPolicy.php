<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

/**
 * Class CategoryPolicy
 *
 * Handles authorization for Category-related actions.
 * Enforces the 'Fail Fast' security principle by validating granular permissions.
 */
final class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user  The authenticated user.
     * @return bool True if the user has the required permission.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::CATEGORY_VIEW->value);
    }

    /**
     * Determine whether the user can view a specific model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Category  $category  The category being accessed.
     * @return bool True if the user has the required permission.
     */
    public function view(User $user, Category $category): bool
    {
        return $user->can(Permission::CATEGORY_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user  The authenticated user.
     * @return bool True if the user has the required permission.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::CATEGORY_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Category  $category  The category being updated.
     * @return bool True if the user has the required permission.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->can(Permission::CATEGORY_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Category  $category  The category being deleted.
     * @return bool True if the user has the required permission.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->can(Permission::CATEGORY_DELETE->value);
    }
}
