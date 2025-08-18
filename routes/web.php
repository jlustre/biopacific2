<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditController;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use App\Livewire\FacilitiesIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Models\Facility;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

// Multi-tenant site routes (with ResolveTenant middleware)
// Route::middleware(['resolve.tenant'])->group(function () {
//     Route::get('/', [HomeController::class, 'index'])->name('home');
// });
Route::get('/', [HomeController::class, 'index'])->name('home');

// Landing page for easy access (without tenant middleware)
Route::get('/index', function () {
    return view('index');
})->name('index');

// Dashboard routes (without tenant middleware - for site directory)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/facility/{id}/preview', [DashboardController::class, 'facility'])->name('dashboard.facility');

    // Facility Admin Routes
    Route::get('/facilities', [App\Http\Controllers\FacilityAdminController::class, 'index'])->name('admin.facilities.index');
    Route::get('/facilities/{id}/edit', [App\Http\Controllers\FacilityAdminController::class, 'edit'])->name('admin.facilities.edit');
    Route::put('/facilities/{id}', [App\Http\Controllers\FacilityAdminController::class, 'update'])->name('admin.facilities.update');
    Route::get('/facilities/{id}/layout-config', [App\Http\Controllers\FacilityAdminController::class, 'layoutConfig'])->name('admin.facilities.layout-config');
    Route::put('/facilities/{id}/layout-config', [App\Http\Controllers\FacilityAdminController::class, 'updateLayoutConfig'])->name('admin.facilities.update-layout-config');

    // Layout Template Admin Routes
    Route::get('/layouts', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'index'])->name('admin.layouts.index');
    Route::get('/layouts/create', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'create'])->name('admin.layouts.create');
    Route::post('/layouts', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'store'])->name('admin.layouts.store');
    Route::get('/layouts/{id}', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'show'])->name('admin.layouts.show');
    Route::get('/layouts/{id}/edit', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'edit'])->name('admin.layouts.edit');
    Route::put('/layouts/{id}', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'update'])->name('admin.layouts.update');
    Route::delete('/layouts/{id}', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'destroy'])->name('admin.layouts.destroy');
    Route::get('/layouts/{id}/preview', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'preview'])->name('admin.layouts.preview');
    Route::post('/layouts/{id}/duplicate', [App\Http\Controllers\Admin\LayoutTemplateController::class, 'duplicate'])->name('admin.layouts.duplicate');

    // Layout Section Admin Routes
    Route::get('/sections', [App\Http\Controllers\Admin\LayoutSectionController::class, 'index'])->name('admin.sections.index');
    Route::get('/sections/create', [App\Http\Controllers\Admin\LayoutSectionController::class, 'create'])->name('admin.sections.create');
    Route::post('/sections', [App\Http\Controllers\Admin\LayoutSectionController::class, 'store'])->name('admin.sections.store');
    Route::get('/sections/{id}', [App\Http\Controllers\Admin\LayoutSectionController::class, 'show'])->name('admin.sections.show');
    Route::get('/sections/{id}/edit', [App\Http\Controllers\Admin\LayoutSectionController::class, 'edit'])->name('admin.sections.edit');
    Route::put('/sections/{id}', [App\Http\Controllers\Admin\LayoutSectionController::class, 'update'])->name('admin.sections.update');
    Route::delete('/sections/{id}', [App\Http\Controllers\Admin\LayoutSectionController::class, 'destroy'])->name('admin.sections.destroy');
    Route::post('/sections/{id}/duplicate', [App\Http\Controllers\Admin\LayoutSectionController::class, 'duplicate'])->name('admin.sections.duplicate');
    Route::get('/sections/{id}/preview/{variant?}', [App\Http\Controllers\Admin\LayoutSectionController::class, 'preview'])->name('admin.sections.preview');

    // Layout Builder Routes
    Route::get('/layout-builder', [App\Http\Controllers\Admin\LayoutBuilderController::class, 'index'])->name('admin.layout-builder.index');
    Route::get('/layout-builder/test', function() {
        return response()->json(['message' => 'Test route works', 'timestamp' => now()]);
    });
    Route::get('/layout-builder/facility/{facilityId}/layout', [App\Http\Controllers\Admin\LayoutBuilderController::class, 'getFacilityLayout'])->name('admin.layout-builder.facility-layout');
    Route::post('/layout-builder/facility/{facilityId}/update', [App\Http\Controllers\Admin\LayoutBuilderController::class, 'updateLayout'])->name('admin.layout-builder.update');
    Route::post('/layout-builder/facility/{facilityId}/duplicate', [App\Http\Controllers\Admin\LayoutBuilderController::class, 'duplicateLayout'])->name('admin.layout-builder.duplicate');
    Route::post('/layout-builder/facility/{facilityId}/save-template', [App\Http\Controllers\Admin\LayoutBuilderController::class, 'saveAsTemplate'])->name('admin.layout-builder.save-template');
    Route::post('/layout-builder/facility/{facilityId}/preview', [App\Http\Controllers\Admin\LayoutBuilderController::class, 'preview'])->name('admin.layout-builder.preview');
});

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

// Add these audit routes
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::get('/audit/{auditLog}', [AuditController::class, 'show'])->name('audit.show');
Route::post('/audit/export', [AuditController::class, 'export'])->name('audit.export');
Route::get('/audit/stats', [AuditController::class, 'stats'])->name('audit.stats');

require __DIR__.'/auth.php';
