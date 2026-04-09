<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
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
