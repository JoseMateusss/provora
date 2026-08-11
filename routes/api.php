<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\Api\V1\QuestionBatchController;
use App\Http\Controllers\Api\V1\QuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthCheckController::class);

    Route::prefix('auth')->group(function () {
        Route::post('/register', RegisterController::class)->middleware('throttle:10,1');
        Route::post('/login', LoginController::class)->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', LogoutController::class);
            Route::get('/me', MeController::class);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('documents', DocumentController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::post('question-batches', [QuestionBatchController::class, 'store'])->middleware('throttle:10,1');
        Route::apiResource('question-batches', QuestionBatchController::class)->only(['index', 'show', 'destroy']);
        Route::apiResource('questions', QuestionController::class)->only(['show', 'update', 'destroy']);
    });
});
