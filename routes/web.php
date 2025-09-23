<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FacilityAdminController;
use App\Livewire\FacilitiesIndex;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Appearance;
use App\Models\Facility;

// Home & Landing
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/index', fn() => view('index'))->name('index');

// Admin Routes (auth + admin role)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->as('admin.')->group(function () {
    // Facility CRUD (use FacilityAdminController)
    // Route::resource('facilities', App\Http\Controllers\Admin\FacilityController::class); // <-- Remove or comment this line

    Route::get('/facilities', [FacilityAdminController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [FacilityAdminController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [FacilityAdminController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility}', [FacilityAdminController::class, 'show'])->name('facilities.show');
    Route::get('/facilities/{facility}/edit', [FacilityAdminController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{facility}', [FacilityAdminController::class, 'update'])->name('facilities.update');
    Route::delete('/facilities/{facility}', [FacilityAdminController::class, 'destroy'])->name('facilities.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/facility/{id}/preview', [DashboardController::class, 'facility'])->name('dashboard.facility');

    Route::get('/facilities/{facility}/hipaa', fn(Facility $facility) => view('admin.facilities.hipaa', compact('facility')))->name('facilities.hipaa');
});

// Facilities (view permission)
Route::middleware(['auth', 'permission:view facilities'])->group(function () {
    Route::get('/facilities', FacilitiesIndex::class)->name('facilities.index');
});

// Facilities (create permission)
// Route::middleware(['auth', 'permission:create facilities'])->group(function () {
//     Route::resource('facilities', \App\Http\Controllers\FacilityController::class);
// });

// User Settings & Facility Show (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('/facility/{facility:slug}', fn(Facility $facility) => view('facility.show', compact('facility')))->name('facility.show');
});

// Audit Routes
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::get('/audit/{auditLog}', [AuditController::class, 'show'])->name('audit.show');
Route::post('/audit/export', [AuditController::class, 'export'])->name('audit.export');
Route::get('/audit/stats', [AuditController::class, 'stats'])->name('audit.stats');

// Tour Booking
Route::post('/tours', [TourController::class, 'store'])->name('tours.store');

// FAQ Section
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');

// Auth routes
require __DIR__.'/auth.php';


