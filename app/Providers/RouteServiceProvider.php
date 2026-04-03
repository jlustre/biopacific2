<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Upload;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();
        // Removed explicit Route::bind for 'upload' to allow default model binding
    }
}
