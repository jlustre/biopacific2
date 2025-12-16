<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IncidentContactController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('incident-contacts', IncidentContactController::class);
});
