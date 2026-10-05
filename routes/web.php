<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomCalendarController;
use App\Http\Controllers\Sarpras\FacilityController;
use App\Http\Controllers\Sarpras\ProcessingController;
use App\Http\Controllers\Sarpras\RoomController;
use App\Http\Controllers\Student\BookingRequestController;
use App\Http\Controllers\Wr2\ApprovalController;
use Illuminate\Support\Facades\Route;

// Public & Authentication
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated shared routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [RoomCalendarController::class, 'index'])->name('calendar.index');
    Route::post('/api/check-availability', [RoomCalendarController::class, 'checkAvailability'])->name('calendar.check');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    // Mahasiswa routes
    Route::middleware('role:MAHASISWA')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookings', [BookingRequestController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingRequestController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingRequestController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking_request}', [BookingRequestController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking_request}/edit', [BookingRequestController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking_request}', [BookingRequestController::class, 'update'])->name('bookings.update');
        Route::post('/bookings/{booking_request}/submit', [BookingRequestController::class, 'submit'])->name('bookings.submit');
        Route::post('/bookings/{booking_request}/cancel', [BookingRequestController::class, 'cancel'])->name('bookings.cancel');
    });

    // WR 2 routes
    Route::middleware('role:WR2')->prefix('wr2')->name('wr2.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');
        Route::get('/approvals/{booking_request}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{booking_request}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{booking_request}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // Sarpras routes
    Route::middleware('role:SARPRAS')->prefix('sarpras')->name('sarpras.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/processing', [ProcessingController::class, 'index'])->name('processing.index');
        Route::get('/processing/history', [ProcessingController::class, 'history'])->name('processing.history');
        Route::get('/processing/{booking_request}', [ProcessingController::class, 'show'])->name('processing.show');
        Route::post('/processing/{booking_request}/start', [ProcessingController::class, 'startProcess'])->name('processing.start');
        Route::post('/processing/{booking_request}/confirm', [ProcessingController::class, 'confirm'])->name('processing.confirm');
        Route::post('/processing/{booking_request}/reject', [ProcessingController::class, 'reject'])->name('processing.reject');
        Route::post('/processing/{booking_request}/cancel', [ProcessingController::class, 'cancel'])->name('processing.cancel');

        Route::resource('rooms', RoomController::class);
        Route::resource('facilities', FacilityController::class)->except(['create', 'show', 'edit']);
    });
});
