<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\DefaultRole;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootSanctum();
        $this->bootScramble();
        $this->bootGate();
    }

    public function bootGate(): void
    {
        Gate::before(function (mixed $user, string $ability): ?bool {
            return $user?->hasRole(DefaultRole::SUPER_ADMIN) ? true : null;
        });
    }

    public function bootSanctum(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    public function bootScramble(): void
    {
        Scramble::configure()->withDocumentTransformers(function (
            OpenApi $openApi,
        ): void {
            $openApi->secure(SecurityScheme::http('bearer'));
        });
    }
}
