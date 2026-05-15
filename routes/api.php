<?php

declare(strict_types=1);

use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CategoryController;
use App\Http\Controllers\Customer\DocumentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\VideoController;
use App\Http\Controllers\Rest\CategoryRestController;
use App\Http\Controllers\Rest\DocumentRestController;
use App\Http\Controllers\Rest\UserRestController;
use App\Http\Controllers\Rest\VideoRestController;
use App\Http\Controllers\SearchController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'can:admin.access', SetLocale::class])
    ->group(function () {
        Route::get('/users', [UserRestController::class, 'index']);
        Route::get('/admins', [UserRestController::class, 'admins']);
        Route::post('/admins', [UserRestController::class, 'storeAdmin']);
        Route::patch('/users/{user}/role', [
            UserRestController::class,
            'updateRole',
        ]);

        // Categories
        Route::apiResource(
            '/categories',
            CategoryRestController::class,
        )->whereUuid('category');

        // Videos
        Route::apiResource('/videos', VideoRestController::class)->whereUuid(
            'video',
        );
        Route::post('/videos/{video}/upload', [
            VideoRestController::class,
            'upload',
        ]);
        Route::post('/videos/{video}/toggle-publish', [
            VideoRestController::class,
            'togglePublish',
        ]);

        // Documents
        Route::apiResource(
            '/documents',
            DocumentRestController::class,
        )->whereUuid('document');
        Route::post('/documents/{document}/upload', [
            DocumentRestController::class,
            'upload',
        ]);
        Route::post('/documents/{document}/toggle-publish', [
            DocumentRestController::class,
            'togglePublish',
        ]);
    });

Route::prefix('auth')
    ->middleware([SetLocale::class])
    ->group(function () {
        Route::prefix('/google')->group(function () {
            Route::get('/redirect', [
                AuthController::class,
                'redirectToGoogle',
            ]);
            Route::get('/callback', [
                AuthController::class,
                'handleGoogleCallback',
            ]);
        });
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [
            AuthController::class,
            'forgotPassword',
        ]);
        Route::post('/reset-password', [
            AuthController::class,
            'resetPassword',
        ]);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [ProfileController::class, 'logout']);
        });
    });

Route::middleware(['auth:sanctum', SetLocale::class])->group(function () {
    // Favorites
    Route::get('/favorites', [
        FavoriteController::class,
        'index',
    ])->name('favorites.index');
    Route::post('/favorites', [
        FavoriteController::class,
        'store',
    ])->name('favorites.store');
    Route::delete('/favorites/{id?}', [
        FavoriteController::class,
        'destroy',
    ])->name('favorites.destroy');
});

Route::prefix('profile')
    ->middleware(['auth:sanctum', SetLocale::class])
    ->group(function () {
        Route::get('/', [ProfileController::class, 'me']);
        Route::patch('/', [ProfileController::class, 'update']);
        Route::patch('/password', [ProfileController::class, 'updatePassword']);
    });

Route::middleware([SetLocale::class])->group(function () {
    // Categories
    Route::apiResource('/categories', CategoryController::class)
        ->names('customer.categories')
        ->whereUuid('category')
        ->only(['index', 'show']);

    Route::middleware(['auth:sanctum'])->group(function () {
        // Videos
        Route::apiResource('/videos', VideoController::class)
            ->names('customer.videos')
            ->whereUuid('video')
            ->only(['index', 'show']);

        // Documents
        Route::get('/documents', [DocumentController::class, 'index'])->name(
            'customer.documents.index',
        );
        Route::get('/documents/{document}/pages', [
            DocumentController::class,
            'getPages',
        ])
            ->name('customer.documents.pages')
            ->whereUuid('document');
        Route::get('/documents/{document}', [DocumentController::class, 'show'])
            ->name('customer.documents.show')
            ->whereUuid('document');

        // Search
        Route::get('/search', [SearchController::class, 'search'])->name(
            'customer.search',
        );
        Route::get('/search/recent', [
            SearchController::class,
            'recentsSearch',
        ])->name('customer.search.recent');
    });
});
