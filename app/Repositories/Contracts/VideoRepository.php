<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Video;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Interface VideoRepository
 *
 * Defines the specific contract for Video repository operations.
 * This interface isolates the database layer from the business logic
 * and ensures data access methods are strictly typed and consistent.
 *
 * @extends Repository<Video>
 *
 * @method QueryBuilder published()
 */
interface VideoRepository extends Repository
{
    /**
     * Retrieve a paginated or filtered list of videos.
     * Eager loads the category relation to prevent N+1 issues.
     *
     * @param  array<string, mixed>  $queries  HTTP query parameters for filtering, sorting, or paginating.
     * @return Collection<int, Video>|Paginator<int, Video>
     */
    public function getVideos(array $queries = []): Collection|Paginator;
}
