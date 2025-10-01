<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\WebmasterContactAdminController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/webmaster-contacts', [WebmasterContactAdminController::class, 'index'])->name('webmaster.contacts.index');
    Route::get('/webmaster-contacts/{contact}', [WebmasterContactAdminController::class, 'show'])->name('webmaster.contacts.show');
});
