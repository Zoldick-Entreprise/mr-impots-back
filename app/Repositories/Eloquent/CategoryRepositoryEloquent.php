<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\CommonRepository;
use App\Repositories\Contracts\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CategoryRepositoryEloquent
 *
 * Eloquent implementation of the CategoryRepository.
 * Handles the data retrieval logic for Category models using spatie/laravel-query-builder.
 *
 * @extends CommonRepository<Category>
 */
final class CategoryRepositoryEloquent extends CommonRepository implements CategoryRepository
{
    /**
     * Class name of the model this repository manages.
     *
     * @var class-string<Category>
     */
    protected string $model = Category::class;

    /**
     * Default sort order for the query builder.
     */
    protected string $defaultSort = '-created_at';

    /**
     * CategoryRepository constructor.
     * Sets up the allowed filters, sorts, and includes for the query builder.
     */
    public function __construct()
    {
        parent::__construct([
            'filters' => ['name', 'slug', 'parent_id'],
            'sorts' => ['id', 'sort_order', 'created_at'],
            'includes' => ['childrens', 'parent'],
            'relations' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRootCategoriesWithChildrens(): Collection
    {
        return $this->buildQuery()
            ->with('childrens')
            ->whereNull('parent_id')
            ->get();
    }
}
