<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Download
 *
 * Represents a document download action.
 * Tracks which user (or guest) downloaded which document, and from which IP.
 *
 * @property string $id
 * @property string|null $user_id
 * @property string $document_id
 * @property string|null $ip_address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 * @property-read Document $document
 */
final class Download extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_id',
        'ip_address',
    ];

    /**
     * Get the user that downloaded the document.
     *
     * @return BelongsTo<User, Download>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the document that was downloaded.
     *
     * @return BelongsTo<Document, Download>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
