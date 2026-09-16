<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DroughtController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NdviController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\RainfallController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WaterBalanceController;
use App\Http\Controllers\WaterNeedController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

// --- Authentication Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// --- Protected Application Routes ---
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Lahan
    Route::resource('lands', LandController::class);

    // Peta Monitoring
    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    // Curah Hujan
    Route::get('/rainfall', [RainfallController::class, 'index'])->name('rainfall.index');
    Route::get('/rainfall/{land}', [RainfallController::class, 'show'])->name('rainfall.show');

    // Prakiraan Cuaca
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
    Route::get('/weather/{land}', [WeatherController::class, 'show'])->name('weather.show');

    // Sentinel-2 & NDVI
    Route::get('/ndvi', [NdviController::class, 'index'])->name('ndvi.index');
    Route::get('/ndvi/{land}', [NdviController::class, 'show'])->name('ndvi.show');

    // Analisis Kekeringan
    Route::get('/drought', [DroughtController::class, 'index'])->name('drought.index');
    Route::get('/drought/{land}', [DroughtController::class, 'show'])->name('drought.show');

    // Neraca Air
    Route::get('/water-balance', [WaterBalanceController::class, 'index'])->name('water-balance.index');
    Route::get('/water-balance/{land}', [WaterBalanceController::class, 'show'])->name('water-balance.show');

    // Kebutuhan Air Tanaman
    Route::get('/water-needs', [WaterNeedController::class, 'index'])->name('water-needs.index');

    // Prioritas Pemberian Air
    Route::get('/priority', [PriorityController::class, 'index'])->name('priority.index');

    // Riwayat
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{land}', [HistoryController::class, 'show'])->name('history.show');

    // Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Pengaturan
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/sync-weather', [SettingController::class, 'syncWeather'])->name('settings.sync-weather');
    Route::post('/settings/clean-demo', [SettingController::class, 'cleanDemo'])->name('settings.clean-demo');
    Route::patch('/settings/crop-coefficient/{cropCoefficient}', [SettingController::class, 'updateCropCoefficient'])->name('settings.crop-coefficient.update');
});
