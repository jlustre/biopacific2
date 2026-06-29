<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\WebmasterContactAdminController;
use App\Http\Controllers\Admin\CareersController;

Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/webmaster-contacts', [WebmasterContactAdminController::class, 'index'])->name('webmaster.contacts.index');
    Route::get('/webmaster-contacts/{contact}', [WebmasterContactAdminController::class, 'show'])->name('webmaster.contacts.show');
    Route::post('/webmaster-contacts/{contact}/update', [WebmasterContactAdminController::class, 'update'])->name('webmaster.contacts.update');
    Route::post('/webmaster-contacts/{contact}/comments', [WebmasterContactAdminController::class, 'storeComment'])->name('webmaster.contacts.comments.store');
    Route::delete('/webmaster-contacts/{contact}', [WebmasterContactAdminController::class, 'destroy'])->name('webmaster.contacts.destroy');
});

