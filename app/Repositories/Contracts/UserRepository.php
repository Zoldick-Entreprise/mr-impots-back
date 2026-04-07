<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface UserRepository
 *
 * Defines the specific contract for User repository operations.
 *
 * @extends Repository<User>
 */
interface UserRepository extends Repository
{
    /**
     * Retrieve users that do not have administration roles (e.g., standard users).
     *
     * @param  array<string, mixed>  $queries  HTTP query parameters
     * @return Collection<int, User>|Paginator<int, User>
     */
    public function getNormalUsers(array $queries = []): Collection|Paginator;

    /**
     * Retrieve users that have administration roles.
     * Hides super-admin accounts unless the requesting user is also a super-admin.
     *
     * @param  User  $currentUser  The user requesting the list
     * @param  array<string, mixed>  $queries  HTTP query parameters
     * @return Collection<int, User>|Paginator<int, User>
     */
    public function getAdminUsers(User $currentUser, array $queries = []): Collection|Paginator;
}
