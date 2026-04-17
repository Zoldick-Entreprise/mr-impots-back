<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Video;
use App\Repositories\CommonRepository;
use App\Repositories\Contracts\VideoRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class VideoRepositoryEloquent
 *
 * Eloquent implementation of the VideoRepository.
 * Handles the data retrieval and encapsulation logic for Video models
 * using spatie/laravel-query-builder for optimized querying.
 *
 * @extends CommonRepository<Video>
 */
final class VideoRepositoryEloquent extends CommonRepository implements VideoRepository
{
    /**
     * Default sort order for the query builder.
     */
    protected string $defaultSort = '-published_at';

    /**
     * Class name of the model this repository manages.
     *
     * @var class-string<Video>
     */
    protected string $model = Video::class;

    /**
     * VideoRepository constructor.
     * Initializes the allowed filters, sorts, and default eager loads
     * to prevent N+1 queries when fetching lists of videos.
     */
    public function __construct()
    {
        parent::__construct([
            'filters' => ['title', 'category_id', 'is_featured', 'published_at'],
            'sorts' => ['id', 'published_at', 'views_count', 'created_at'],
            'includes' => ['category', 'media'],
            'relations' => ['category'],
        ]);
    }

    /**
     * Retrieve a paginated or filtered list of videos.
     * Leverages the base query builder and gracefully handles pagination or collection returns.
     *
     * @param  array<string, mixed>  $queries  HTTP query parameters for filtering, sorting, or paginating.
     * @return Collection<int, Video>|Paginator<int, Video>
     */
    public function getVideos(array $queries = []): Collection|Paginator
    {
        return $this->handleMaybePaginatedQuery(function () {
            return $this->buildQuery();
        }, $queries);
    }
}
