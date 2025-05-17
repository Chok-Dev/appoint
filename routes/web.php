<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth']);

// Auth routes provided by Laravel
require __DIR__ . '/auth.php';

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Protected routes requiring authentication
Route::middleware(['auth'])->group(function () {

    // Group routes
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/', [GroupController::class, 'index'])->name('index');
        Route::get('/create', [GroupController::class, 'create'])->name('create');
        Route::post('/', [GroupController::class, 'store'])->name('store');
        Route::get('/{group}', [GroupController::class, 'show'])->name('show');
        Route::get('/{group}/edit', [GroupController::class, 'edit'])->name('edit');
        Route::put('/{group}', [GroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');
    });

    // Clinic routes
    Route::prefix('clinics')->name('clinics.')->group(function () {
        Route::get('/', [ClinicController::class, 'index'])->name('index');
        Route::get('/create', [ClinicController::class, 'create'])->name('create');
        Route::post('/', [ClinicController::class, 'store'])->name('store');
        Route::get('/{clinic}', [ClinicController::class, 'show'])->name('show');
        Route::get('/{clinic}/edit', [ClinicController::class, 'edit'])->name('edit');
        Route::put('/{clinic}', [ClinicController::class, 'update'])->name('update');
        Route::delete('/{clinic}', [ClinicController::class, 'destroy'])->name('destroy');
    });

    // Doctor routes
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/create', [DoctorController::class, 'create'])->name('create');
        Route::post('/', [DoctorController::class, 'store'])->name('store');
        Route::get('/{doctor}', [DoctorController::class, 'show'])->name('show');
        Route::get('/{doctor}/edit', [DoctorController::class, 'edit'])->name('edit');
        Route::put('/{doctor}', [DoctorController::class, 'update'])->name('update');
        Route::delete('/{doctor}', [DoctorController::class, 'destroy'])->name('destroy');
    });

    // TimeSlot routes
    Route::prefix('timeslots')->name('timeslots.')->group(function () {
        Route::get('/', [TimeSlotController::class, 'index'])->name('index');
        Route::get('/schedule', [TimeSlotController::class, 'schedule'])->name('schedule');
        Route::get('/create', [TimeSlotController::class, 'create'])->name('create');
        Route::post('/', [TimeSlotController::class, 'store'])->name('store');
        Route::get('/{timeSlot}', [TimeSlotController::class, 'show'])->name('show');
        Route::get('/{timeSlot}/edit', [TimeSlotController::class, 'edit'])->name('edit');
        Route::put('/{timeSlot}', [TimeSlotController::class, 'update'])->name('update');
        Route::delete('/{timeSlot}', [TimeSlotController::class, 'destroy'])->name('destroy');
    });

    // Appointment routes
    // Inside the appointments route group
    Route::get('/get-clinics-by-group', [AppointmentController::class, 'getClinicsByGroup'])->name('get.clinics.by.group');
    Route::get('/get-available-dates', [AppointmentController::class, 'getAvailableDates'])->name('get.available.dates');
    Route::get('/search-patient', [AppointmentController::class, 'searchPatient'])->name('search.patient');
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/{appointment}/print', [AppointmentController::class, 'print'])->name('print');

        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/create', [AppointmentController::class, 'create'])->name('create');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update');
        Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');

        // Admin-only route
        Route::middleware(['admin'])->group(function () {
            Route::post('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('updateStatus');
        });
    });

    // User management routes (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // AJAX routes
    Route::get('/get-doctors', [AppointmentController::class, 'getDoctors'])->name('get.doctors');
    Route::get('/get-timeslots', [AppointmentController::class, 'getTimeSlots'])->name('get.timeslots');
});
