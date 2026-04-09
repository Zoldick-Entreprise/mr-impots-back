<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// Media
class Video extends Model implements HasMedia
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
            ->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
}
