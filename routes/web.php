<?php

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use App\Livewire\FacilitiesIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Models\Facility;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'permission:view facilities'])->group(function () {
    Route::get('/facilities', FacilitiesIndex::class)->name('facilities.index');
});

// Add this resource route for facilities if not already present

Route::middleware(['auth', 'permission:create facilities'])->group(function () {
    Route::resource('facilities', \App\Http\Controllers\FacilityController::class);
    // Route::get('/facilities/create', [FacilityController::class, 'create'])->name('facilities.create');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Add the facilities.index route
    // Route::get('/facilities', FacilitiesIndex::class)->name('facilities.index');

    // The facility.show route
    Route::get('/facility/{facility:slug}', function (Facility $facility) {
        return view('facility.show', compact('facility'));
    })->name('facility.show');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
