<?php

use App\Http\Controllers\Api\LandApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('lands')->group(function () {
    Route::get('/', [LandApiController::class, 'index'])->name('api.lands.index');
    Route::get('/{land}', [LandApiController::class, 'show'])->name('api.lands.show');
    Route::get('/{land}/ndvi', [LandApiController::class, 'ndvi'])->name('api.lands.ndvi');
    Route::get('/{land}/rainfall', [LandApiController::class, 'rainfall'])->name('api.lands.rainfall');
    Route::get('/{land}/water-balance', [LandApiController::class, 'waterBalance'])->name('api.lands.water-balance');
});

Route::get('/priority', [LandApiController::class, 'priority'])->name('api.priority');
