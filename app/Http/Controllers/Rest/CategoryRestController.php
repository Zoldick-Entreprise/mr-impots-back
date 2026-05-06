<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\AdminCategoryResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class CategoryRestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $this->authorize('viewAny', Category::class);

        return CategoryResource::collection($this->categoryRepository->getRootCategoriesWithChildrens());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = $this->categoryRepository->create($request->only(['name', 'slug']));

        if ($request->has('childrens')) {
            $category->childrens()->createMany($request->childrens);
        }

        return new AdminCategoryResource($category->load('childrens'))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $category = $this->categoryRepository->retrieve($id);

        $this->authorize('view', $category);

        return new AdminCategoryResource($category->load('childrens'))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        $category = $this->categoryRepository->retrieve($id);

        $this->authorize('update', $category);

        $category = $this->categoryRepository->update(
            $id,
            $request->validated(),
        );

        return new AdminCategoryResource($category)->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = $this->categoryRepository->whereFirstOrFail(['id' => $id]);

        $this->authorize('delete', $category);

        $this->categoryRepository->delete($id);

        return $this->successResponse(status: Response::HTTP_NO_CONTENT);
    }
}
