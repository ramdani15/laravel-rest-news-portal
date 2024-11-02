<?php

use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;

Route::name('api.v1.')
    ->prefix('v1')
    ->group(function () {

        // Auth
        Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
            Route::post('login', 'login')->name('login')->middleware('throttle:10,1');
            Route::post('signup', 'signup')->name('signup')->middleware('throttle:10,1');

            Route::middleware('auth:sanctum')->group(function () {
                // Logout
                Route::post('logout', 'logout')->name('logout');
            });
        });

        // Dashboard
        Route::resource('dashboard', DashboardController::class)->only(['index', 'show']);
        Route::get('dashboard/{id}/comments', [DashboardController::class, 'comments'])->name('dashboard.comments');

        Route::middleware('auth:sanctum')->group(function () {
            // Article
            Route::resource('articles', ArticleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::group(['prefix' => 'articles/{id}', 'name' => 'articles.'], function () {
                Route::post('request-approval', [ArticleController::class, 'requestApproval'])->name('request-approval');
                Route::post('approve', [ArticleController::class, 'approve'])->name('approve')->middleware(IsAdmin::class);
                Route::post('reject', [ArticleController::class, 'reject'])->name('reject')->middleware(IsAdmin::class);
                Route::post('publish', [ArticleController::class, 'publish'])->name('publish');
                Route::post('unpublish', [ArticleController::class, 'unpublish'])->name('unpublish');
                Route::post('toggle-reaction', [ArticleController::class, 'toggleReaction'])->name('toggle-reaction');
            });

            // Comment
            Route::resource('comments', CommentController::class)->only(['store']);
            Route::post('comments/{id}/reply', [CommentController::class, 'reply'])->name('comments.reply');
            Route::post('comments/{id}/toggle-reaction', [CommentController::class, 'toggleReaction'])->name('comments.toggle-reaction');

            // Profile
            Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
                Route::get('/', 'index')->name('profile.index');
                Route::patch('/', 'update')->name('profile.update');
            });

        });
    });
