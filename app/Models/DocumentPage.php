<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

#[Fillable(['document_id', 'locale', 'page_number', 'content', 'embedding'])]
final class DocumentPage extends Model
{
    use HasFactory, HasUuids, Searchable;

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'locale' => $this->locale,
            'page_number' => $this->page_number,
            'content' => $this->content,
        ];
    }
}
