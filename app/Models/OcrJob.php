<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OcrStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        'document_id',
        'locale',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ]),
]
final class OcrJob extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => OcrStatus::class,
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
