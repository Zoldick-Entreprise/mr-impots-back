<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\DefaultRole;
use App\Enums\Permission;
use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class DocumentPolicy
 *
 * Handles authorization for Document resources.
 * Enforces business rules: Admins have full access, Editors can only modify their own documents.
 */
final class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Admins bypass all specific policy checks and are granted full access.
     *
     * @param  User  $user  The user attempting the action.
     * @param  string  $ability  The action being attempted.
     * @return bool|null True if the user is an admin, null to fall through to specific checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(DefaultRole::SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  User  $user  The user attempting to view the list of documents.
     * @return bool True if the user is at least an editor.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::DOCUMENT_READ->value);
    }

    /**
     * Determine whether the user can view a specific model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Document  $document  The Document being accessed.
     * @return bool True if the user has the required permission.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->can(Permission::DOCUMENT_READ->value);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user  The authenticated user.
     * @return bool True if the user has the required permission.
     */
    public function create(User $user): bool
    {
        return $user->can(Permission::DOCUMENT_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Document  $document  The Document being updated.
     * @return bool True if the user has the required permission.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->can(Permission::DOCUMENT_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user  The authenticated user.
     * @param  Document  $document  The Document being deleted.
     * @return bool True if the user has the required permission.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->can(Permission::DOCUMENT_DELETE->value);
    }
}
