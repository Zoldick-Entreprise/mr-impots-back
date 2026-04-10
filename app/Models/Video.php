<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use FFMpeg\Coordinate\TimeCode;


/**
 * Represents a user in the system.
 *
 * @property string $id The unique identifier of the video.
 * @property null|int $category_id The parent ID of the category.
 * @property string $title The title of the video.
 * @property string $description The description of the video.
 * @property string $video_url The URL of the video.
 * @property string $thumbnail_url The URL of the thumbnail.
 * @property bool $is_featured Whether the video is featured.
 * @property int $views_count The number of views for the video.
 * @property \Carbon\Carbon $published_at The publication timestamp of the video.
 * @property-read \Carbon\Carbon $created_at The creation timestamp of the video.
 * @property-read \Carbon\Carbon $updated_at The last update timestamp of the video.
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Media $avatar The avatar media of the video.
 */

// Media
final class Video extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $with = ['media'];

    protected $fillable = [
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'category_id',
        'is_featured',
        'views_count',
        'published_at',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'published_at' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->extractVideoFrameAtSecond(1)
            ->width(300)
            ->height(300);
    }
}
