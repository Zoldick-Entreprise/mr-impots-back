<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Models\Video;

/**
 * Class VideoPolicy
 *
 * Handles authorization for Video-related actions.
 * Enforces the 'Fail Fast' security principle by validating granular permissions.
 */
final class VideoPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user  The authenticated user.
     * @return bool True if the user has the required permission.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::VIDEO_VIEW->value);
    }

    /**
     * Determine whether the user can view a specific model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Video  $video  The video being accessed.
     * @return bool True if the user has the required permission.
     */
    public function view(User $user, Video $video): bool
    {
        return $user->can(Permission::VIDEO_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user  The authenticated user.
     * @return bool True if the user has the required permission.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::VIDEO_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Video  $video  The video being updated.
     * @return bool True if the user has the required permission.
     */
    public function update(User $user, Video $video): bool
    {
        return $user->can(Permission::VIDEO_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Video  $video  The video being deleted.
     * @return bool True if the user has the required permission.
     */
    public function delete(User $user, Video $video): bool
    {
        return $user->can(Permission::VIDEO_DELETE->value);
    }
}
