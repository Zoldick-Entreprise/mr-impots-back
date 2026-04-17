<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface CategoryRepository
 *
 * Defines the specific contract for Category repository operations.
 * This interface isolates the database layer from the business logic.
 *
 * @extends Repository<Category>
 */
interface CategoryRepository extends Repository
{
    /**
     * Retrieve all root categories (categories without a parent) and eager load their children.
     * Ordered by sort_order for consistent display.
     *
     * @return Collection<int, Category>
     */
    public function getRootCategoriesWithChildren(): Collection;
}
