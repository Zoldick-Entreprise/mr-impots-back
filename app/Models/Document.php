<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\OcrStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

use function Illuminate\Support\now;

/**
 * Class Document
 *
 * Represents a document in the system. It handles PDF files in multiple languages
 * (e.g., French and English) using Spatie Media Library, minimizing redundant DB records.
 *
 * @property string $id The unique identifier for the document.
 * @property string $title The title of the document(in the specified language of the user).
 * @property string $description The description of the document.
 * @property string $category_id The ID of the category to which the document belongs.
 * @property DocumentStatus $status The status of the document.
 * @property OcrStatus $ocr_status The OCR status of the document.
 * @property string $uploaded_by The ID of the user who uploaded the document.
 * @property Carbon|null $published_at The date and time the document was published.
 * @property int $document_views The number of times the document has been viewed.
 * @property Carbon|null $created_at The date and time the document was created.
 * @property Carbon|null $updated_at The date and time the document was last updated.
 * @property-read ?string $fr_document The link of the file in french.
 * @property-read ?string $en_document The link of the file in english.
 */
#[
    Fillable([
        'category_id',
        'status',
        'ocr_status',
        'uploaded_by',
        'published_at',
        'document_views',
        'title',
        'description',
    ]),
]
final class Document extends Model implements HasMedia
{
    use HasFactory, HasTranslations, HasUuids, InteractsWithMedia;

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = ['title', 'description'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'published_at' => 'datetime',
        'document_views' => 'integer',
        'status' => DocumentStatus::class,
        'ocr_status' => OcrStatus::class,
    ];

    protected $with = ['category'];

    public function frDocument(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::remember(
                'fr_document_'.$this->id,
                ttl: fn ($url) => $url !== null ? 3570 : null,
                callback: fn () => $this->getFirstMedia(
                    'document_fr',
                )?->getTemporaryUrl(now()->addHour()),
            ),
        );
    }

    public function enDocument(): Attribute
    {
        return Attribute::make(
            get: fn () => Cache::remember(
                'en_document_'.$this->id,
                ttl: fn ($url) => $url !== null ? 3570 : null,
                callback: fn () => $this->getFirstMedia(
                    'document_en',
                )?->getTemporaryUrl(now()->addHour()),
            ),
        );
    }

    /**
     * Register the media collections for Spatie Media Library.
     * Restricts uploads to PDF files and ensures a single file per language collection.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document_fr')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('r2')
            ->singleFile();

        $this->addMediaCollection('document_en')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('r2')
            ->singleFile();
    }

    /**
     * Get the category that the document belongs to.
     *
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who uploaded the document.
     *
     * @return BelongsTo<User>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the textual contents extracted from the document (e.g., via OCR).
     *
     * @return HasMany<DocumentPage>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(DocumentPage::class);
    }

    /**
     * Get the related OCR Jobs processing this document (one per locale).
     *
     * @return HasMany<OcrJob>
     */
    public function ocrJobs(): HasMany
    {
        return $this->hasMany(OcrJob::class);
    }

    // /**
    //  * Get the tags associated with the document.
    //  */
    // public function tags(): BelongsToMany
    // {
    //     return $this->belongsToMany(Tag::class);
    // }
}
