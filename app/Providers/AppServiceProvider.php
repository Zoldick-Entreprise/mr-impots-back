<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\DefaultRole;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
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
        $this->setupValidationRules();
    }

    private function bootGate(): void
    {
        Gate::before(function (mixed $user, string $ability): ?bool {
            return $user?->hasRole(DefaultRole::SUPER_ADMIN) ? true : null;
        });
    }

    private function bootSanctum(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    private function bootScramble(): void
    {
        Scramble::configure()->withDocumentTransformers(function (
            OpenApi $openApi,
        ): void {
            $openApi->secure(SecurityScheme::http('bearer'));
        });
    }

    private function setupValidationRules(): void
    {
        Validator::extend('unique_in_array', function ($attribute, $value, $parameters, $validator) {
            $field = $parameters[0] ?? null;

            if (! $field || ! is_array($value)) {
                return true;
            }

            $values = collect($value)->pluck($field)->filter();

            return $values->count() === $values->unique()->count();
        });

        Validator::replacer('unique_in_array', function ($message, $attribute, $rule, $parameters) {
            $field = $parameters[0] ?? 'field';

            return "The {$field} values in {$attribute} must be unique.";
        });
    }
}
