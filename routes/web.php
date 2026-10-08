<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/courses', 'placeholder')->name('courses');
    Route::view('/fees', 'placeholder')->name('fees');
    Route::view('/payments', 'placeholder')->name('payments');
    Route::view('/reports', 'placeholder')->name('reports');
    Route::view('/settings', 'settings')->name('settings');
    
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('courses', CourseController::class);
    Route::resource('students', StudentController::class);
});

require __DIR__.'/auth.php';