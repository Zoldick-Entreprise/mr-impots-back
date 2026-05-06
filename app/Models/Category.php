<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

/**
 * Represents a category in the system.
 *
 * @property string $id The unique identifier of the category.
 * @property null|int $parent_id The parent ID of the category.
 * @property array $name The name of the category.
 * @property string $slug The slug of the category.
 * @property null|string $icon The icon of the category.
 * @property int $sort_order The sort order of the category.
 * @property-read Carbon $created_at The creation timestamp of the user.
 * @property-read Carbon $updated_at The last update timestamp of the user.
 * @property-read Media $avatar The avatar media of the user.
 */
#[Fillable(['parent_id', 'name', 'slug', 'icon'])]
final class Category extends Model
{
    use HasTranslations, HasUuids;

    /**
     * The attributes that are translatable.
     */
    protected $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the parent category of this category.
     *
     * @return BelongsTo<Category>
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories of this category.
     *
     * @return HasMany<Category>
     */
    public function childrens()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the videos associated with this category.
     *
     * @return HasMany<Video>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
