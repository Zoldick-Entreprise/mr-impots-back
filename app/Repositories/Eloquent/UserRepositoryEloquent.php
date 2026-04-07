<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\DefaultRole;
use App\Models\User;
use App\Repositories\CommonRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UserRepositoryEloquent
 *
 * Eloquent implementation of the UserRepository.
 * Handles the data retrieval logic for User models using spatie/laravel-query-builder.
 *
 * @extends CommonRepository<User>
 */
final class UserRepositoryEloquent extends CommonRepository implements UserRepository
{
    /**
     * Class name of the model this repository manages.
     *
     * @var class-string<User>
     */
    protected string $model = User::class;

    /**
     * UserRepository constructor.
     * Sets up the allowed filters, sorts, and includes for the query builder.
     */
    public function __construct()
    {
        parent::__construct([
            'filters' => ['name', 'email'],
            'sorts' => ['id', 'name', 'created_at'],
            'includes' => ['roles', 'permissions'],
            'relations' => [],
        ]);
    }

    /**
     * Retrieve users that do not have administration roles (e.g., standard users).
     *
     * @param  array<string, mixed>  $queries  HTTP query parameters
     * @return Collection<int, User>|Paginator<int, User>
     */
    public function getNormalUsers(array $queries = []): Collection|Paginator
    {
        return $this->handleMaybePaginatedQuery(function () {
            return $this->buildQuery()
                ->whereDoesntHave('roles', function (Builder $query) {
                    $query->whereIn('name', [DefaultRole::ADMIN, DefaultRole::SUPER_ADMIN]);
                });
        }, $queries);
    }

    /**
     * Retrieve users that have administration roles.
     * Hides super-admin accounts unless the requesting user is also a super-admin.
     *
     * @param  User  $currentUser  The user requesting the list
     * @param  array<string, mixed>  $queries  HTTP query parameters
     * @return Collection<int, User>|Paginator<int, User>
     */
    public function getAdminUsers(User $currentUser, array $queries = []): Collection|Paginator
    {
        return $this->handleMaybePaginatedQuery(function () use ($currentUser) {
            $query = $this->buildQuery()
                ->whereHas('roles', function (Builder $q) {
                    $q->whereIn('name', [DefaultRole::ADMIN, DefaultRole::SUPER_ADMIN]);
                });

            // Hide super-admins from standard admins
            if (! $currentUser->hasRole(DefaultRole::SUPER_ADMIN)) {
                $query->whereDoesntHave('roles', function (Builder $q) {
                    $q->where('name', DefaultRole::SUPER_ADMIN);
                });
            }

            return $query;
        }, $queries);
    }
}
