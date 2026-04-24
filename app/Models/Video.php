<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

/**
 * Represents a video in the system.
 *
 * @property string $id The unique identifier of the video.
 * @property null|string $category_id The parent ID of the category.
 * @property string $title The title of the video.
 * @property string $description The description of the video.
 * @property-read null|string $video_url The dynamically generated URL of the video.
 * @property-read null|string $thumbnail_url The dynamically generated URL of the thumbnail.
 * @property bool $is_featured Whether the video is featured.
 * @property int $views_count The number of views for the video.
 * @property Carbon $published_at The publication timestamp of the video.
 * @property-read Carbon $created_at The creation timestamp of the video.
 * @property-read Carbon $updated_at The last update timestamp of the video.
 * @property-read Media $avatar The avatar media of the video.
 */

// Media
final class Video extends Model implements HasMedia
{
    use HasTranslations, HasUuids, InteractsWithMedia;

    /**
     * The attributes that are appended to the model's array representation.
     *
     * @var array
     */
    protected $appends = ['video_url', 'thumbnail_url'];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'description',
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
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the video URL dynamically from Spatie Media Library.
     *
     * @return Attribute<string|null, never>
     */
    protected function videoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::remember(
                "video_url_{$this->id}",
                ttl: fn ($url) => $url !== null ? 3570 : 0,
                callback: fn () => $this->getFirstMedia('video')?->getTemporaryUrl(now()->addHour())
            ),
        );
    }

    /**
     * Get the thumbnail URL dynamically from Spatie Media Library.
     *
     * @return Attribute<string|null, never>
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::remember(
                "thumbnail_url_{$this->id}",
                ttl: fn ($url) => $url !== null ? 3570 : 0,
                callback: fn () => $this->getFirstMedia('video')?->getTemporaryUrl(now()->addHour(), 'preview')
            ),
        );
    }

    /**
     * Scope a query to only include published videos.
     *
     * @param  Builder<Video>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('published_at', '<=', now());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')
            ->useDisk('r2')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->extractVideoFrameAtSecond(1)
            ->performOnCollections('video')
            ->width(300)
            ->height(300);
    }
}
