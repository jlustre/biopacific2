<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\WebmasterContactAdminController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/webmaster-contacts', [WebmasterContactAdminController::class, 'index'])->name('webmaster.contacts.index');
    Route::get('/webmaster-contacts/{contact}', [WebmasterContactAdminController::class, 'show'])->name('webmaster.contacts.show');
    Route::post('/webmaster-contacts/{contact}/update', [WebmasterContactAdminController::class, 'update'])->name('webmaster.contacts.update');
    Route::delete('/webmaster-contacts/{contact}', [WebmasterContactAdminController::class, 'destroy'])->name('webmaster.contacts.destroy');
});
