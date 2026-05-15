<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait HasFavorites
 *
 * Adds polymorphic favorites relation to a model.
 */
trait HasFavorites
{
    /**
     * Get all of the model's favorites.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Determine if the model is favorited by the given user.
     */
    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        // Check if favorites relation is loaded (to prevent N+1 queries if already eager loaded)
        if ($this->relationLoaded('favorites')) {
            return $this->favorites->contains('user_id', $user->id);
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
