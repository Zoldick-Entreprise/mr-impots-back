<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResearchResource;
use App\Models\DocumentPage;
use App\Models\SearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Meilisearch\Endpoints\Indexes;

class SearchController extends Controller
{
    /**
     * Search documents content.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'lang' => ['nullable', 'string', 'in:fr,en'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = $validated['q'];
        $lang = $validated['lang'] ?? null;
        $category = $validated['category'] ?? null;
        $page = $validated['page'] ?? 1;

        $search = DocumentPage::search($query, function (
            $meilisearch,
            $query,
            $options
        ) {
            if ($meilisearch instanceof Indexes) {
                $options['attributesToHighlight'] = ['content'];
                $options['highlightPreTag'] = '<mark>';
                $options['highlightPostTag'] = '</mark>';

                return $meilisearch->search($query, $options);
            }

            return $meilisearch;
        });

        if ($lang) {
            $search->where('locale', $lang);
        }

        if ($category) {
            $search->where('category', $category);
        }

        $results = $search->paginate(15, 'page', $page);

        // Async logging
        dispatch(function () use ($query, $results, $lang, $request) {
            SearchLog::create([
                'user_id' => $request->user()?->id,
                'query' => $query,
                'results_count' => $results->total(),
                'language' => $lang,
                'ip' => $request->ip(),
            ]);
        })->afterResponse();

        return DocumentResearchResource::collection($results)
            ->response();
    }

    /**
     * Returns the 10 recent search results for the authenticated user
     */
    public function recentsSearch(Request $request): JsonResponse
    {
        $results = SearchLog::where('user_id', $request->user()?->id)
            ->orderBy('created_at', 'desc')
            ->get();
        // ->paginate(10)

        return response()->json($results);
    }
}
