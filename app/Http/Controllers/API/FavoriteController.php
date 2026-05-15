<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteRequest;
use App\Models\Document;
use App\Models\Favorite;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class FavoriteController
 *
 * Handles API requests for user favorites (polymorphic).
 */
class FavoriteController extends Controller
{
    /**
     * Display a paginated list of the authenticated user's favorites.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Eager load the polymorphic relation to avoid N+1 queries
        $favorites = $user
            ->favorites()
            ->with('favoritable')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($favorites);
    }

    /**
     * Store a newly created favorite in storage.
     */
    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $user = $request->user();
        $modelClass = $request->getFavoritableModelClass();
        $favoritableId = $request->input('favoritable_id');

        // Check if the target item actually exists
        if (! $modelClass::where('id', $favoritableId)->exists()) {
            return response()->json(
                [
                    'message' => 'The requested '.
                        $request->input('favoritable_type').
                        ' does not exist.',
                ],
                404,
            );
        }

        // Check for duplicates
        $exists = $user
            ->favorites()
            ->where('favoritable_type', $modelClass)
            ->where('favoritable_id', $favoritableId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already in favorites'], 409);
        }

        $favorite = $user->favorites()->create([
            'favoritable_type' => $modelClass,
            'favoritable_id' => $favoritableId,
        ]);

        return response()->json(
            [
                'message' => 'Favorite added successfully',
                'data' => $favorite,
            ],
            201,
        );
    }

    /**
     * Remove the specified favorite from storage.
     * Supports both deletion by favorite ID or by polymorphic reference.
     */
    public function destroy(Request $request, ?string $id = null): JsonResponse
    {
        $user = $request->user();

        // If ID is provided, delete by Favorite ID
        if ($id) {
            $favorite = $user->favorites()->find($id);
            if (! $favorite) {
                return response()->json(
                    ['message' => 'Favorite not found'],
                    404,
                );
            }
            $favorite->delete();

            return response()->json(
                ['message' => 'Favorite removed successfully'],
                200,
            );
        }

        // Delete by polymorphic type & id if passed in query string
        $type = $request->query('type');
        $favoritableId = $request->query('id');

        if ($type && $favoritableId) {
            $modelClass = match (strtolower($type)) {
                'document' => Document::class,
                'video' => Video::class,
                default => null,
            };

            if (! $modelClass) {
                return response()->json(
                    ['message' => 'Invalid type specified'],
                    400,
                );
            }

            $deletedCount = $user
                ->favorites()
                ->where('favoritable_type', $modelClass)
                ->where('favoritable_id', $favoritableId)
                ->delete();

            if ($deletedCount > 0) {
                return response()->json(
                    ['message' => 'Favorite removed successfully'],
                    200,
                );
            }
        }

        return response()->json(['message' => 'Favorite not found'], 404);
    }
}
