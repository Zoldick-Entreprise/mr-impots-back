<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'can:admin.access', SetLocale::class])
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/admins', [AdminUserController::class, 'admins']);
        Route::post('/admins', [AdminUserController::class, 'storeAdmin']);
        Route::patch('/users/{user}/role', [
            AdminUserController::class,
            'updateRole',
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
            Route::post('/logout', [UserController::class, 'logout']);
        });
    });

Route::prefix('profile')
    ->middleware(['auth:sanctum', SetLocale::class])
    ->group(function () {
        Route::get('/', [UserController::class, 'me']);
        Route::patch('/', [UserController::class, 'update']);
        Route::patch('/password', [UserController::class, 'updatePassword']);
    });
