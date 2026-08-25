<?php

use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\WebhookApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:120,1'])->prefix('v1')->group(function () {
    Route::get('/tasks', [TaskApiController::class, 'index'])->name('api.tasks.index');
    Route::post('/tasks', [TaskApiController::class, 'store'])->name('api.tasks.store');
    Route::get('/tasks/{task}', [TaskApiController::class, 'show'])->name('api.tasks.show');
    Route::patch('/tasks/{task}/status', [TaskApiController::class, 'updateStatus'])->name('api.tasks.update-status');
    Route::post('/tasks/{task}/comments', [TaskApiController::class, 'addComment'])->name('api.tasks.add-comment');

    Route::get('/webhooks', [WebhookApiController::class, 'index']);
    Route::post('/webhooks', [WebhookApiController::class, 'store']);
    Route::delete('/webhooks/{endpoint}', [WebhookApiController::class, 'destroy']);
});
