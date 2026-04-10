<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a user in the system.
 *
 * @property string $id The unique identifier of the category.
 * @property null|int $parent_id The parent ID of the category.
 * @property string $name The name of the category.
 * @property string $slug The slug of the category.
 * @property null|string $icon The icon of the category.
 * @property int $sort_order The sort order of the category.
 * @property-read Carbon $created_at The creation timestamp of the user.
 * @property-read Carbon $updated_at The last update timestamp of the user.
 * @property-read Media $avatar The avatar media of the user.
 */


final class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
