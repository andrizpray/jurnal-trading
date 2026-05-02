<?php

use App\Http\Controllers\Api\TradingCalculatorController;
use App\Http\Controllers\Api\TradingPreviewController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:web')->group(function () {
    Route::post('/calculate-plan', [TradingCalculatorController::class, 'calculate']);
    Route::get('/challenge/{challenge}/chart-data', [TradingCalculatorController::class, 'chartData']);
    
    // Real-time preview API
    Route::prefix('preview')->group(function () {
        Route::post('/', [TradingPreviewController::class, 'preview']);
        Route::post('/lot-size', [TradingPreviewController::class, 'calculateLotSize']);
        Route::get('/currency-pairs', [TradingPreviewController::class, 'currencyPairs']);
        Route::get('/trader-configs', [TradingPreviewController::class, 'traderConfigs']);
    });
    
    // Notifications API
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);
    });
});
