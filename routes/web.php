<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Auth routes provided by Laravel
require __DIR__.'/auth.php';

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Protected routes requiring authentication
Route::middleware(['auth'])->group(function () {
    // Group routes
    Route::resource('groups', GroupController::class);
    
    // Clinic routes
    Route::resource('clinics', ClinicController::class);
    
    // Doctor routes
    Route::resource('doctors', DoctorController::class);
    
    // TimeSlot routes
    Route::resource('timeslots', TimeSlotController::class);
    
    // Appointment routes
    Route::resource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    
    // Admin-only routes
    Route::middleware(['admin'])->group(function () {
        Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    });
    
    // AJAX routes
    Route::get('/get-doctors', [AppointmentController::class, 'getDoctors'])->name('get.doctors');
    Route::get('/get-timeslots', [AppointmentController::class, 'getTimeSlots'])->name('get.timeslots');
});