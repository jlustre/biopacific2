<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('arbitration-templates', App\Http\Controllers\Admin\ArbitrationTemplateController::class);
});
