<?php

use App\Filament\Pages\PoolDetail;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\test;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth'], function () {
    $cacheableMiddleware = 'cache.headers:public;no_cache;max_age=300;stale_if_error;etag';
    // Redirect
    Route::get('/', function () {
        return redirect('main-dashboard');
    });
    Route::redirect('/dashboard-default', '/main-dashboard');


    // Main Dashboard
    Route::get('/main-dashboard', [\App\Http\Controllers\Pool\MainDashboardController::class, 'index'])->middleware([$cacheableMiddleware])->name('main-dashboard');


    Route::get('/detail', [\App\Http\Controllers\Pool\DetailedController::class, 'index'])->name('detail')->middleware([$cacheableMiddleware]);
    Route::get('/detail/export', [\App\Http\Controllers\Pool\DetailedController::class, 'export'])->name('detailed-dashboard.export');


    // Auth
    Route::get('/logout', [SessionController::class, 'destroy']);


    // Settings
    Route::get('/app-settings', [\App\Http\Controllers\AppSettingsController::class, 'index'])->name('app-settings');
    Route::post('/app-settings', [\App\Http\Controllers\AppSettingsController::class, 'store'])->name('app-settings');

    // Settings Parameter
    Route::get('/settings-parameter', [\App\Http\Controllers\ParameterController::class, 'index'])->name('settings.parameter');
    Route::post('/settings-parameter', [\App\Http\Controllers\ParameterController::class, 'store'])->name('settings.parameter');
    Route::get('/settings-parameter-livewire', \App\Livewire\SettingsParameter::class)->name('settings.parameter.livewire');


    // Advanced Settings
    Route::get('/score-simulation', [\App\Http\Controllers\ScoreSimulationController::class, 'index'])->name('score-simulation');
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store']);
    Route::get('/login/forgot-password', [ChangePasswordController::class, 'create']);
    Route::post('/forgot-password', [ChangePasswordController::class, 'sendEmail']);
    Route::get('/reset-password/{token}', [ChangePasswordController::class, 'resetPass'])->name('password.reset');
    Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
    Route::get('/', function () {
        return redirect('/login');
    });
});
