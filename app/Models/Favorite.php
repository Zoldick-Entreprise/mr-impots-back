<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Class Favorite
 *
 * Represents a user's favorite item (Document, Video, etc.).
 *
 * @property int $id
 * @property int $user_id
 * @property string $favoritable_type
 * @property int $favoritable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Favorite extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'favoritable_type', 'favoritable_id'];

    /**
     * Get the parent favoritable model (Document or Video).
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that favorited the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
