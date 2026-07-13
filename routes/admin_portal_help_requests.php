<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PortalHelpRequestAdminController;

Route::middleware(['auth', 'role:admin|super-admin|rdhr'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/portal-help-requests', [PortalHelpRequestAdminController::class, 'index'])->name('portal-help-requests.index');
    Route::get('/portal-help-requests/{portalHelpRequest}', [PortalHelpRequestAdminController::class, 'show'])->name('portal-help-requests.show');
    Route::post('/portal-help-requests/{portalHelpRequest}/update', [PortalHelpRequestAdminController::class, 'update'])->name('portal-help-requests.update');

    Route::redirect('/portal-help-recipients', '/admin/communications/employee-email-mappings?activeTab=portal-help')
        ->name('portal-help-recipients.index');
});
