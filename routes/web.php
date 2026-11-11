<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\MpesaCallbackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return redirect()->route(
        auth()->check() ? 'dashboard' : 'login'
    );
});

// M-Pesa Safaricom Callback
// Public endpoint: Safaricom does not need to log in.
Route::post(
    '/payments/mpesa/callback/{secret}',
    MpesaCallbackController::class
)->name('payments.mpesa.callback');

// Test email (local environment only)
if (app()->environment('local')) {
    Route::get('/test-mail', function () {
        Mail::raw(
            'Congratulations! Laravel can send email.',
            function ($message) {
                $message->to('zackariaabdisalam@gmail.com')
                    ->subject('Laravel Email Test');
            }
        );

        return 'Test email sent successfully. Check your inbox.';
    })->name('test.mail');
}

// Authenticated application routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Courses
    Route::resource('courses', CourseController::class);

    // Students
    Route::resource('students', StudentController::class);

    // Enrollments
    Route::resource('enrollments', EnrollmentController::class)
        ->except(['show']);

    // Fees
    Route::resource('fees', FeeController::class)
        ->except(['show']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])
        ->name('payments.index');

    Route::get('/payments/create', [PaymentController::class, 'create'])
        ->name('payments.create');

    Route::post('/payments', [PaymentController::class, 'store'])
        ->name('payments.store');

    // Reports
    Route::redirect('/reports', '/reports/payments')
        ->name('reports');

    Route::get('/reports/payments', ReportController::class)
        ->name('reports.index');

    // Settings
    Route::view('/settings', 'settings')
        ->name('settings');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Authentication
require __DIR__.'/auth.php';