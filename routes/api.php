<?php

use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\JspController;
use Illuminate\Support\Facades\Route;

Route::prefix('jsp')->group(function () {
    Route::post('register', [JspController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [JspController::class, 'login'])->middleware('throttle:10,1');
    Route::middleware('jsp.token')->group(function () {
        Route::get('me', [JspController::class, 'me']);
        Route::get('dashboard', [JspController::class, 'dashboard']);
        Route::get('favorites', [JspController::class, 'favorites']);
        Route::get('books/{book}/community', [CommunityController::class, 'show'])->whereNumber('book');
        Route::match(['post', 'delete'], 'books/{book}/favorite', [
            CommunityController::class,
            'favorite',
        ])->whereNumber('book');
        Route::match(['post', 'put', 'delete'], 'books/{book}/review', [
            CommunityController::class,
            'review',
        ])->whereNumber('book');
        Route::get('{module}/options', [JspController::class, 'options']);
        Route::get('{module}', [JspController::class, 'index']);
        Route::get('{module}/{id}', [JspController::class, 'show'])->whereNumber('id');
        Route::post('{module}', [JspController::class, 'save']);
        Route::put('{module}/{id}', [JspController::class, 'save'])->whereNumber('id');
        Route::delete('{module}/{id}', [JspController::class, 'destroy'])->whereNumber('id');
    });
});
