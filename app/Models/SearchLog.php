<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SearchLog
 *
 * Log searches to track users intent and results count.
 */
class SearchLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'query',
        'results_count',
        'language',
        'ip',
    ];

    /**
     * Get the user who performed the search, if any.
     *
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
