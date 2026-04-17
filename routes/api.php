<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CategoryController as CustomerCategoryController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\VideoController as CustomerVideoController;
use App\Http\Controllers\Rest\CategoryController;
use App\Http\Controllers\Rest\UserController;
use App\Http\Controllers\Rest\VideoController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'can:admin.access', SetLocale::class])
    ->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/admins', [UserController::class, 'admins']);
        Route::post('/admins', [UserController::class, 'storeAdmin']);
        Route::patch('/users/{user}/role', [
            UserController::class,
            'updateRole',
        ]);

        // Categories
        Route::apiResource('/categories', CategoryController::class);

        // Videos
        Route::apiResource('/videos', VideoController::class);
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

Route::prefix('profile')
    ->middleware(['auth:sanctum', SetLocale::class])
    ->group(function () {
        Route::get('/', [ProfileController::class, 'me']);
        Route::patch('/', [ProfileController::class, 'update']);
        Route::patch('/password', [ProfileController::class, 'updatePassword']);
    });

Route::middleware(['auth:sanctum', SetLocale::class])->group(function () {
    // Categories
    Route::get('/categories', [CustomerCategoryController::class, 'index']);
    Route::get('/categories/{category}', [CustomerCategoryController::class, 'show']);

    // Videos
    Route::get('/videos', [CustomerVideoController::class, 'index']);
    Route::get('/videos/{id}', [CustomerVideoController::class, 'show']);
});
