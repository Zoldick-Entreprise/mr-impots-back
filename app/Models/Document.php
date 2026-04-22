<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\OcrStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Document
 *
 * Represents a document in the system. It handles PDF files in multiple languages
 * (e.g., French and English) using Spatie Media Library, minimizing redundant DB records.
 *
 * @property string $id
 * @property array $title
 * @property string $category_id
 * @property DocumentStatus $status
 * @property OcrStatus $ocr_status
 * @property string $uploaded_by
 * @property Carbon|null $published_at
 * @property int $document_views
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Document extends Model implements HasMedia
{
    use HasFactory;
    use HasUuids;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'category_id',
        'status',
        'ocr_status',
        'uploaded_by',
        'published_at',
        'document_views',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'title' => 'array',
        'published_at' => 'datetime',
        'document_views' => 'integer',
        'status' => DocumentStatus::class,
        'ocr_status' => OcrStatus::class,
    ];

    protected $with = ['category'];

    /**
     * Register the media collections for Spatie Media Library.
     * Restricts uploads to PDF files and ensures a single file per language collection.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document_fr')
            ->acceptsMimeTypes(['application/pdf'])
            ->singleFile();

        $this->addMediaCollection('document_en')
            ->acceptsMimeTypes(['application/pdf'])
            ->singleFile();
    }

    /**
     * Get the category that the document belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope a query to only include documents available in a specific language.
     *
     * @param  Builder  $query  The query builder instance.
     * @param  string  $language  The language code (e.g., 'fr', 'en').
     */
    public function scopeLanguage(Builder $query, string $language): Builder
    {
        return $query->whereNotNull("title->{$language}");
    }

    // /**
    //  * Get the textual contents extracted from the document (e.g., via OCR).
    //  */
    // public function contents(): HasMany
    // {
    //     return $this->hasMany(DocumentContent::class);
    // }

    // /**
    //  * Get the related OCR Job processing this document.
    //  */
    // public function ocrJob(): HasOne
    // {
    //     return $this->hasOne(OcrJob::class);
    // }

    // /**
    //  * Get the tags associated with the document.
    //  */
    // public function tags(): BelongsToMany
    // {
    //     return $this->belongsToMany(Tag::class);
    // }
}
