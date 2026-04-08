<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'google_id', 'preferred_language'])]
#[Hidden(['password', 'remember_token'])]
/**
 * Represents a user in the system.
 *
 * @property string $id The unique identifier of the user.
 * @property string $name The name of the user.
 * @property string $email The email address of the user.
 * @property null|string $password The password of the user.
 * @property null|string $google_id The Google ID of the user.
 * @property string $preferred_language The preferred language of the user.
 * @property-read Carbon $created_at The creation timestamp of the user.
 * @property-read Carbon $updated_at The last update timestamp of the user.
 * @property-read Media $avatar The avatar media of the user.
 */
final class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, InteractsWithMedia, Notifiable;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['avatar'];

    /**
     * The relationships to eager load by default.
     *
     * @var array<int, string>
     */
    protected $with = ['media'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Register the media collections for the user.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    /**
     * Get the user's avatar URL.
     */
    public function avatar(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('avatar') ?: null);
    }
}
