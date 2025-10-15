<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacilityAdminController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\FaqController;
use App\Livewire\FacilitiesIndex;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Appearance;
use App\Models\Facility;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CareersController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\CareersApplicationsController;


// Public Facility Route (similar to admin preview but public access)

// Webmaster Contact
Route::get('/{facility:slug}/webmaster/contact', function(App\Models\Facility $facility) {
    return view('webmaster.contact', [
        'facility' => $facility,
        'sections' => ['topbar'],
        'sectionVariances' => ['topbar' => 'legal'],
    ]);
})->name('webmaster.contact');
Route::post('/webmaster/contact', [App\Http\Controllers\WebmasterController::class, 'submit'])->name('webmaster.contact.submit');


// Home & Landing
use Illuminate\Support\Facades\Auth;

Route::get('/', function() {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard.index');
        }
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/index', fn() => view('index'))->name('index');

// Redirect old privacy-policy route to new one
Route::get('/{facility:slug}/privacy-policy', function (Facility $facility) {
    return redirect()->route('privacy.policy', ['facility' => $facility->slug]);
});

Route::get('/{facility:slug}/privacy-policy', function (Facility $facility) {
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
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [FacilityAdminController::class, 'dashboard'])->name('dashboard.index');
    Route::get('/facilities', [FacilityAdminController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [FacilityAdminController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [FacilityAdminController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility:slug}', [FacilityAdminController::class, 'show'])->name('facilities.show');
    Route::get('/facilities/{facility}/edit', [FacilityAdminController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{facility}', [FacilityAdminController::class, 'update'])->name('facilities.update');
    Route::post('/facilities/{facility}/services', [FacilityAdminController::class, 'updateServices'])->name('facilities.updateServices');
    // User Management CRUD
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    // List testimonials (new route for index)
    Route::get('/facilities/web-contents/testimonials', [FacilityAdminController::class, 'testimonials'])->name('facilities.webcontents.testimonials');
    Route::get('/facilities/web-contents/testimonials/{facility}/data', [FacilityAdminController::class, 'getTestimonials'])->name('facilities.webcontents.testimonials.data');
    Route::get('/facilities/web-contents/testimonials/{testimonial}', [FacilityAdminController::class, 'showTestimonial'])->name('facilities.webcontents.testimonials.show');
    Route::post('/facilities/web-contents/testimonials', [FacilityAdminController::class, 'storeTestimonial'])->name('facilities.webcontents.testimonials.store');
    Route::put('/facilities/web-contents/testimonials/{testimonial}', [FacilityAdminController::class, 'updateTestimonial'])->name('facilities.webcontents.testimonials.update');
    Route::delete('/facilities/web-contents/testimonials/{testimonial}', [FacilityAdminController::class, 'destroyTestimonial'])->name('facilities.webcontents.testimonials.destroy');
    
    Route::get('/facilities/web-contents/faqs', [FacilityAdminController::class, 'faqs'])->name('facilities.webcontents.faqs');
    Route::get('/facilities/web-contents/faqs/{facility}/data', [FacilityAdminController::class, 'getFaqs'])->name('facilities.webcontents.faqs.data');
    Route::get('/facilities/web-contents/faqs/{faq}', [FacilityAdminController::class, 'showFaq'])->name('facilities.webcontents.faqs.show');
    Route::post('/facilities/web-contents/faqs', [FacilityAdminController::class, 'storeFaq'])->name('facilities.webcontents.faqs.store');
    Route::put('/facilities/web-contents/faqs/{faq}', [FacilityAdminController::class, 'updateFaq'])->name('facilities.webcontents.faqs.update');
    Route::delete('/facilities/web-contents/faqs/{faq}', [FacilityAdminController::class, 'destroyFaq'])->name('facilities.webcontents.faqs.destroy');
    Route::get('/facilities/web-contents/faqs/defaults/list', [FacilityAdminController::class, 'getDefaultFaqs'])->name('facilities.webcontents.faqs.defaults');
    
    Route::get('/admin/facilities/web-contents/news-events', [FacilityAdminController::class, 'newsEvents'])->name('facilities.webcontents.news-events');
    Route::get('/facilities/{facility}/news-events', [FacilityAdminController::class, 'manageNewsEvents'])->name('facilities.news-events.manage');
    Route::get('/facilities/web-contents/blogs', [FacilityAdminController::class, 'blogs'])->name('facilities.webcontents.blogs');
    
    Route::get('/{facility:slug}/admin', fn(Facility $facility) => view('facility.show', compact('facility')))->name('facility.show.admin');
    Route::get('/facility/{id}/preview', [DashboardController::class, 'facility'])->name('dashboard.facility');
    
    Route::get('/facilities/{facility}/hipaa', fn(Facility $facility) => view('facilities.hipaa', compact('facility')))->name('facilities.hipaa');
    
    // Interactive HIPAA checklist for testing
    Route::get('/facilities/{facility}/hipaa-interactive', fn(Facility $facility) => view('facilities.hipaa-interactive', compact('facility')))->name('facilities.hipaa.interactive');
    
    // Service CRUD routes
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    
    // News management using FacilityAdminController
    Route::resource('news', NewsController::class)->names('news');
    Route::delete('news/{news}/delete-image', [NewsController::class, 'deleteImage'])->name('news.deleteImage');
    Route::resource('admin/events', EventController::class)->names('events');

    // Gallery image management
    Route::resource('galleries', GalleryController::class)->except(['show']);
    // Gallery index for a specific facility
    Route::get('/facilities/{facility}/galleries', [GalleryController::class, 'index'])->name('facilities.galleries.index');
    // Gallery image creation for a specific facility
    Route::get('/galleries/{facility}/create', [GalleryController::class, 'create'])->name('facilities.galleries.create');

    Route::post('/facilities/{facility}/gallery/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
    Route::delete('/gallery/{image}', [GalleryController::class, 'delete'])->name('gallery.delete');
    // Move gallery image up/down
    Route::post('/gallery/{image}/move/{direction}', [GalleryController::class, 'move'])->name('gallery.move');
    // Clear all gallery images for a facility
    Route::post('/facilities/{facility}/gallery/clear', [GalleryController::class, 'clearFacility'])->name('gallery.clear');
    Route::post('/facilities/{facility}/hipaa/toggle', [FacilityController::class, 'toggleHipaaFlag'])->name('hipaa.toggle');
});


// Careers CRUD and applications routes
Route::prefix('admin/facilities/webcontents')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('careers', [CareersController::class, 'index'])->name('admin.facilities.webcontents.careers');
    Route::post('careers', [CareersController::class, 'store'])->name('admin.facilities.webcontents.careers.store');
    Route::put('careers/{jobOpening}', [CareersController::class, 'update'])->name('admin.facilities.webcontents.careers.update');
    Route::delete('careers/{jobOpening}', [CareersController::class, 'destroy'])->name('admin.facilities.webcontents.careers.destroy');
    Route::get('careers/{jobOpening}/applications', [CareersController::class, 'applications'])->name('admin.facilities.webcontents.careers.applications');
    Route::put('careers/{jobOpening}/applications/{jobApplication}', [CareersController::class, 'updateApplication'])->name('admin.facilities.webcontents.careers.applications.update');
    Route::delete('careers/{jobOpening}/applications/{jobApplication}', [CareersController::class, 'destroyApplication'])->name('admin.facilities.webcontents.careers.applications.destroy');
    Route::get('careers/applications/{jobApplication}/details', [CareersController::class, 'applicationDetails'])->name('admin.facilities.webcontents.careers.applications.details');
});


// General dashboard and user settings for all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('settings/profile', function() {
        return view('profile.edit');
    })->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

// Audit Routes
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::get('/audit/{auditLog}', [AuditController::class, 'show'])->name('audit.show');
Route::post('/audit/export', [AuditController::class, 'export'])->name('audit.export');
Route::get('/audit/stats', [AuditController::class, 'stats'])->name('audit.stats');

// Tour Booking
Route::get('/book', [TourController::class, 'showForm'])->name('tours.form');
Route::post('/tours', [TourController::class, 'store'])->name('tours.store');

// FAQ Section
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');


// Register admin webmaster contacts routes
require __DIR__.'/admin_webmaster_contacts.php';

// Auth routes
require __DIR__.'/auth.php';

// Public-facing job application route
use App\Http\Controllers\CareersPublicController;

Route::post('/careers/apply', [CareersPublicController::class, 'apply'])->name('careers.apply');

// Public Facility Route (catch-all, must be last)
Route::get('/{facility:slug}', [FacilityController::class, 'publicView'])->name('facility.public');

// Job applications viewing route
Route::get('/applications/{id}', [JobApplicationController::class, 'show'])->name('applications.show');

// List job applications for a facility
Route::get('/facilities/{facility}/applications', [CareersApplicationsController::class, 'index'])->name('facilities.applications.index');


