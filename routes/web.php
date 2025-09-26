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

// Public Facility Route (similar to admin preview but public access)
Route::get('/facility/{facility:slug}', function (Facility $facility) {
    $colors = [
        'primary' => $facility->primary_color ?? '#059669',
        'secondary' => $facility->secondary_color ?? '#064E3B',
        'accent' => $facility->accent_color ?? '#FACC15'
    ];

    $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
    $sections = [];
    $sectionVariances = [];
    $layoutTemplate = 'default-template';

    if ($activeWebContent && $activeWebContent->sections) {
        if (is_string($activeWebContent->sections)) {
            $sections = json_decode($activeWebContent->sections, true) ?? [];
        } elseif (is_array($activeWebContent->sections)) {
            $sections = $activeWebContent->sections;
        }
    }

    if ($activeWebContent && isset($activeWebContent->variances)) {
        if (is_string($activeWebContent->variances)) {
            $sectionVariances = json_decode($activeWebContent->variances, true) ?? [];
        } elseif (is_array($activeWebContent->variances)) {
            $sectionVariances = $activeWebContent->variances;
        }
    }

    $layoutTemplate = $activeWebContent ? $activeWebContent->layout_template : 'default-template';

    // Fetch FAQ data for dynamic FAQ section
    $faqs = \App\Models\Faq::all();
    $categories = \App\Models\Faq::select('category')->distinct()->pluck('category')->filter()->values()->all();

    // Fetch testimonials for the facility
    $testimonials = \App\Models\Testimonial::where('facility_id', $facility->id)
        ->where('is_active', true)
        ->orderBy('is_featured', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    return view('welcome', [
        'facility' => $facility->toArray(),
        'colors' => $colors,
        'sections' => $sections,
        'sectionVariances' => $sectionVariances,
        'layoutTemplate' => $layoutTemplate,
        'faqs' => $faqs,
        'categories' => $categories,
        'testimonials' => $testimonials
    ]);
})->name('facility.public');

// Privacy Policy
Route::get('/{facility:slug}/privacy-policy', function (Facility $facility) {
    // Format facility data like the welcome view does
    $facilityData = $facility->toArray();
    $colors = [
        'primary' => $facility->primary_color ?? '#047857',
        'secondary' => $facility->secondary_color ?? '#1f2937', 
        'accent' => $facility->accent_color ?? '#06b6d4'
    ];
    
    // Get the facility's web content to determine sections
    $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
    $sections = ['topbar']; // Always include topbar for navigation
    $sectionVariances = ['topbar' => 'legal'];
    
    if ($activeWebContent && $activeWebContent->sections) {
        if (is_string($activeWebContent->sections)) {
            $additionalSections = json_decode($activeWebContent->sections, true) ?? [];
        } elseif (is_array($activeWebContent->sections)) {
            $additionalSections = $activeWebContent->sections;
        }
        
        if (!empty($additionalSections) && is_array($additionalSections)) {
            $sections = array_merge($sections, $additionalSections);
        }
    }
    
    if ($activeWebContent && isset($activeWebContent->variances)) {
        if (is_string($activeWebContent->variances)) {
            $additionalVariances = json_decode($activeWebContent->variances, true) ?? [];
        } elseif (is_array($activeWebContent->variances)) {
            $additionalVariances = $activeWebContent->variances;
        }
        
        if (!empty($additionalVariances) && is_array($additionalVariances)) {
            $sectionVariances = array_merge($sectionVariances, $additionalVariances);
        }
    }
    
    // Force legal topbar variant for legal pages (must be after merging)
    $sectionVariances['topbar'] = 'legal';
    
    return view('privacy-policy', [
        'facility' => $facilityData,
        'colors' => $colors,
        'sections' => $sections,
        'sectionVariances' => $sectionVariances
    ]);
})->name('privacy.policy');

// Notice of Privacy Practices
Route::get('/{facility:slug}/notice-of-privacy-practices', function (Facility $facility) {
    // Format facility data like the welcome view does
    $facilityData = $facility->toArray();
    $colors = [
        'primary' => $facility->primary_color ?? '#047857',
        'secondary' => $facility->secondary_color ?? '#1f2937', 
        'accent' => $facility->accent_color ?? '#06b6d4'
    ];
    
    // Get the facility's web content to determine sections
    $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
    $sections = ['topbar']; // Always include topbar for navigation
    $sectionVariances = ['topbar' => 'legal'];
    
    if ($activeWebContent && $activeWebContent->sections) {
        if (is_string($activeWebContent->sections)) {
            $additionalSections = json_decode($activeWebContent->sections, true) ?? [];
        } elseif (is_array($activeWebContent->sections)) {
            $additionalSections = $activeWebContent->sections;
        }
        
        if (!empty($additionalSections) && is_array($additionalSections)) {
            $sections = array_merge($sections, $additionalSections);
        }
    }
    
    if ($activeWebContent && isset($activeWebContent->variances)) {
        if (is_string($activeWebContent->variances)) {
            $additionalVariances = json_decode($activeWebContent->variances, true) ?? [];
        } elseif (is_array($activeWebContent->variances)) {
            $additionalVariances = $activeWebContent->variances;
        }
        
        if (!empty($additionalVariances) && is_array($additionalVariances)) {
            $sectionVariances = array_merge($sectionVariances, $additionalVariances);
        }
    }
    
    // Force legal topbar variant for legal pages (must be after merging)
    $sectionVariances['topbar'] = 'legal';
    
    return view('notice-privacy-practices', [
        'facility' => $facilityData,
        'colors' => $colors,
        'sections' => $sections,
        'sectionVariances' => $sectionVariances
    ]);
})->name('notice.privacy.practices');

// Terms of Service
Route::get('/{facility:slug}/terms-of-service', function (Facility $facility) {
    // Format facility data like the welcome view does
    $facilityData = $facility->toArray();
    $colors = [
        'primary' => $facility->primary_color ?? '#047857',
        'secondary' => $facility->secondary_color ?? '#1f2937', 
        'accent' => $facility->accent_color ?? '#06b6d4'
    ];
    
    // Get the facility's web content to determine sections
    $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
    $sections = ['topbar']; // Always include topbar for navigation
    $sectionVariances = ['topbar' => 'legal'];
    
    if ($activeWebContent && $activeWebContent->sections) {
        if (is_string($activeWebContent->sections)) {
            $additionalSections = json_decode($activeWebContent->sections, true) ?? [];
        } elseif (is_array($activeWebContent->sections)) {
            $additionalSections = $activeWebContent->sections;
        }
        
        if (!empty($additionalSections) && is_array($additionalSections)) {
            $sections = array_merge($sections, $additionalSections);
        }
    }
    
    if ($activeWebContent && isset($activeWebContent->variances)) {
        if (is_string($activeWebContent->variances)) {
            $additionalVariances = json_decode($activeWebContent->variances, true) ?? [];
        } elseif (is_array($activeWebContent->variances)) {
            $additionalVariances = $activeWebContent->variances;
        }
        
        if (!empty($additionalVariances) && is_array($additionalVariances)) {
            $sectionVariances = array_merge($sectionVariances, $additionalVariances);
        }
    }
    
    // Force legal topbar variant for legal pages (must be after merging)
    $sectionVariances['topbar'] = 'legal';
    
    return view('terms-of-service', [
        'facility' => $facilityData,
        'colors' => $colors,
        'sections' => $sections,
        'sectionVariances' => $sectionVariances
    ]);
})->name('terms.service');

// Accessibility
Route::get('/{facility:slug}/accessibility', function (Facility $facility) {
    // Format facility data like the welcome view does
    $facilityData = $facility->toArray();
    $colors = [
        'primary' => $facility->primary_color ?? '#047857',
        'secondary' => $facility->secondary_color ?? '#1f2937', 
        'accent' => $facility->accent_color ?? '#06b6d4'
    ];
    
    // Get the facility's web content to determine sections
    $activeWebContent = $facility->webcontents()->where('is_active', true)->first();
    $sections = ['topbar']; // Always include topbar for navigation
    $sectionVariances = ['topbar' => 'legal'];
    
    if ($activeWebContent && $activeWebContent->sections) {
        if (is_string($activeWebContent->sections)) {
            $additionalSections = json_decode($activeWebContent->sections, true) ?? [];
        } elseif (is_array($activeWebContent->sections)) {
            $additionalSections = $activeWebContent->sections;
        }
        
        if (!empty($additionalSections) && is_array($additionalSections)) {
            $sections = array_merge($sections, $additionalSections);
        }
    }
    
    if ($activeWebContent && isset($activeWebContent->variances)) {
        if (is_string($activeWebContent->variances)) {
            $additionalVariances = json_decode($activeWebContent->variances, true) ?? [];
        } elseif (is_array($activeWebContent->variances)) {
            $additionalVariances = $activeWebContent->variances;
        }
        
        if (!empty($additionalVariances) && is_array($additionalVariances)) {
            $sectionVariances = array_merge($sectionVariances, $additionalVariances);
        }
    }
    
    // Force legal topbar variant for legal pages (must be after merging)
    $sectionVariances['topbar'] = 'legal';
    
    return view('accessibility', [
        'facility' => $facilityData,
        'colors' => $colors,
        'sections' => $sections,
        'sectionVariances' => $sectionVariances
    ]);
})->name('accessibility');

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
    Route::get('/admin/facility/{facility:slug}', fn(Facility $facility) => view('facility.show', compact('facility')))->name('facility.show');
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


