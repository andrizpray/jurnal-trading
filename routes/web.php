<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TradingPlanController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Trading Plan
    Route::get('/trading-plan', [TradingPlanController::class, 'index'])->name('trading-plan.index');
    Route::post('/trading-plan', [TradingPlanController::class, 'store'])->name('trading-plan.store');
    Route::get('/trading-plan/history', [TradingPlanController::class, 'history'])->name('trading-plan.history');
    Route::get('/trading-plan/{plan}/export/excel', [TradingPlanController::class, 'exportExcel'])->name('trading-plan.export.excel');
    Route::get('/trading-plan/{plan}/export/pdf', [TradingPlanController::class, 'exportPdf'])->name('trading-plan.export.pdf');

    // Challenge
    Route::get('/challenge', [ChallengeController::class, 'index'])->name('challenge.index');
    Route::post('/challenge/start', [ChallengeController::class, 'start'])->name('challenge.start');
    Route::get('/challenge/{challenge}', [ChallengeController::class, 'show'])->name('challenge.show');
    Route::patch('/challenge/{challenge}/day/{day}', [ChallengeController::class, 'updateDay'])->name('challenge.updateDay');
    Route::delete('/challenge/{challenge}', [ChallengeController::class, 'reset'])->name('challenge.reset');

    // Journal
    Route::resource('journal', JournalController::class);
    Route::get('/journal/export/excel', [JournalController::class, 'exportExcel'])->name('journal.export.excel');
    Route::get('/journal/export/pdf', [JournalController::class, 'exportPdf'])->name('journal.export.pdf');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Notifications web view
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index'])
        ->name('notifications.index');
});

require __DIR__ . '/auth.php';
