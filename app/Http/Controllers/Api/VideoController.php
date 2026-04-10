<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::with('category')
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json($videos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|array',
            'description' => 'required|array',
            'category_id' => 'required|exists:categories,id',
            'published_at' => 'required|date',
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:204800',
        ]);

        $video = Video::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'published_at' => $request->published_at,
        ]);

        if ($request->hasFile('video')) {
            $video->addMedia($request->file('video'))
                ->toMediaCollection('videos', 'r2_videos');

            $video->update([
                'video_url' => $video->getFirstMediaUrl('videos'),
                'thumbnail_url' => $video->getFirstMediaUrl('videos', 'thumb'),
            ]);
        }

        return response()->json($video, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $video = Video::with('category')->findOrFail($id);

        return response()->json($video);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $video = Video::findOrFail($id);

        $request->validate([
            'title' => 'required|array',
            'description' => 'required|array',
            'category_id' => 'required|exists:categories,id',
            'published_at' => 'required|date',
        ]);

        $video->update([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'published_at' => $request->published_at,
        ]);

        return response()->json($video);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $video = Video::findOrFail($id);
        $video->delete();

        return response()->json(null, 204);
    }
}
