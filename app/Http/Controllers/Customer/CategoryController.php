<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class CategoryController
 *
 * Handles category retrieval for the customer-facing API.
 * This controller is restricted to read-only operations to ensure safety.
 */
final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {}

    /**
     * Display a structured listing of root categories with their childrens.
     */
    public function index(): ResourceCollection
    {
        // Retrieves only root categories and eager loads theirs childrens
        // preventing huge flat payloads and structuring the data for the UI
        $categories = $this->categoryRepository->getRootCategoriesWithChildrens();

        return CategoryResource::collection($categories);
    }

    /**
     * Display a specific category with its immediate childrens.
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load('childrens'));
    }
}
