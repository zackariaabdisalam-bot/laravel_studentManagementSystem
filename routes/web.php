<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    foreach (['students', 'courses', 'attendance', 'fees', 'payments', 'reports'] as $module) {
        Route::get('/'.$module, [ManagementController::class, 'index'])
            ->defaults('module', $module)
            ->name($module.'.index');

        if ($module !== 'reports') {
            Route::post('/'.$module, [ManagementController::class, 'store'])
                ->defaults('module', $module)
                ->name($module.'.store');
            Route::put('/'.$module.'/{record}', [ManagementController::class, 'update'])
                ->defaults('module', $module)
                ->whereNumber('record')
                ->name($module.'.update');
            Route::delete('/'.$module.'/{record}', [ManagementController::class, 'destroy'])
                ->defaults('module', $module)
                ->whereNumber('record')
                ->name($module.'.destroy');
        }
    }
});

require __DIR__.'/auth.php';
