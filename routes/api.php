<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ForgotPasswordController::class, 'reset']);

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('me', [UserController::class, 'me']);

    Route::prefix('admin')
        ->namespace('App\Http\Controllers\Admin')
        ->middleware(['admin'])
        ->group(function () {
            Route::patch('users/{id}/active', [AdminUserController::class, 'updateActiveStatus']);
        });
});
