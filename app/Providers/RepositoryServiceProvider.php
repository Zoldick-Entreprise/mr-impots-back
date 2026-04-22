<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\CategoryRepository;
use App\Repositories\Contracts\DocumentRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Contracts\VideoRepository;
use App\Repositories\Eloquent\CategoryRepositoryEloquent;
use App\Repositories\Eloquent\DocumentRepositoryEloquent;
use App\Repositories\Eloquent\UserRepositoryEloquent;
use App\Repositories\Eloquent\VideoRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 *
 * Registers repository interface bindings to their specific Eloquent implementations
 * within the Laravel IoC container.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
        $this->app->bind(
            CategoryRepository::class,
            CategoryRepositoryEloquent::class,
        );
        $this->app->bind(
            VideoRepository::class,
            VideoRepositoryEloquent::class,
        );
        $this->app->bind(
            DocumentRepository::class,
            DocumentRepositoryEloquent::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
