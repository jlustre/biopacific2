<?php
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityAdminController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminPermissionController;
use App\Http\Controllers\BaaRegistryController;
use App\Http\Controllers\AdminRoleAssignmentController;

use App\Http\Controllers\FacilityController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\CareersController;
use App\Http\Controllers\CareersPublicController;
use App\Http\Controllers\BookATourController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\CareersApplicationsController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\NoticeOfPrivacyPracticesController;
use App\Http\Controllers\TermsOfServiceController;
use App\Http\Controllers\AccessibilityController;
use App\Http\Controllers\AdminJobApplicationController;
use App\Http\Controllers\AdminMfaController;
use App\Http\Controllers\AdminAuthenticatedSessionController;
use App\Http\Controllers\TourRequestController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PreEmploymentController;
use App\Http\Controllers\HireApplicantController;
use App\Http\Controllers\EmployeeApplicationController;
use App\Models\Facility;
use App\Models\Position;


// Job Description Template AJAX/CRUD routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/job-description-templates', [\App\Http\Controllers\Admin\JobOpeningController::class, 'getTemplatesForTitle'])->name('admin.job_description_templates.index');
    Route::post('/job-description-templates', [\App\Http\Controllers\Admin\JobOpeningController::class, 'storeTemplate'])->name('admin.job_description_templates.store');
    Route::put('/job-description-templates/{id}', [\App\Http\Controllers\Admin\JobOpeningController::class, 'updateTemplate'])->name('admin.job_description_templates.update');
    Route::delete('/job-description-templates/{id}', [\App\Http\Controllers\Admin\JobOpeningController::class, 'destroyTemplate'])->name('admin.job_description_templates.destroy');
    Route::get('/job-descriptions/by-position/{position}', [\App\Http\Controllers\Admin\JobOpeningController::class, 'getJobDescriptionsByPosition']);
});

// Allow public access for AJAX department/position select
Route::get('/admin/positions/all', function() {
    return \App\Models\Position::join('departments', 'positions.department_id', '=', 'departments.id')
        ->select('positions.id', 'positions.title', 'departments.name as department')
        ->orderBy('positions.title')
        ->get();
});

// Add another Confidential Reference Check (AJAX or POST)
Route::post('/my-pre-employment/reference-checks/add', [\App\Http\Controllers\PreEmploymentController::class, 'addReferenceCheck'])
    ->middleware(['auth'])
    ->name('pre-employment.reference-checks.add');

// Delete a Confidential Reference Check
Route::delete('/my-pre-employment/reference-checks/{referenceCheck}', [\App\Http\Controllers\PreEmploymentController::class, 'deleteReferenceCheck'])
    ->middleware(['auth'])
    ->name('pre-employment.reference-checks.delete');

// Save/Update a Confidential Reference Check
Route::post('/my-pre-employment/reference-checks/{referenceCheck}', [\App\Http\Controllers\PreEmploymentController::class, 'saveReferenceCheck'])
    ->middleware(['auth'])
    ->name('pre-employment.reference-checks.save');

// Pre-Employment2 Portal (authenticated users)
Route::get('/my-pre-employment2', [\App\Http\Controllers\PreEmployment2Controller::class, 'portal'])
    ->middleware(['auth'])
    ->name('pre-employment2.portal');

// Dashboard and HR Portal routes, grouped by role to avoid duplication
Route::middleware(['auth'])->group(function () {
    // Admin: access to all dashboards and HR portal
        Route::middleware('role:admin|hrrd|facility-admin|facility-dsd')->group(function () {
            Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
            Route::get('/admin/hr-portal', [\App\Http\Controllers\Admin\HrPortalController::class, 'index'])->name('hr-portal.index');
            Route::get('/hr-portal', [\App\Http\Controllers\Admin\HrPortalController::class, 'index'])->name('user.hr-portal');
        });
        // Add explicit route for hrrd, facility-admin, facility-dsd
        Route::middleware('role:hrrd|facility-admin|facility-dsd')->group(function () {
            Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
        });
    // HRRD, facility-admin, facility-dsd: HR portal and user dashboard
    Route::middleware('role:admin|hrrd|facility-admin|facility-dsd')->group(function () {
        Route::get('/admin/hr-portal', [\App\Http\Controllers\Admin\HrPortalController::class, 'index'])->name('hr-portal.index');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/hr-portal', [\App\Http\Controllers\Admin\HrPortalController::class, 'index'])->name('user.hr-portal');
    });
    // User: user dashboard only
    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    });
});
// Facility-specific admin dashboard
Route::middleware(['auth', 'role:admin|hrrd|facility-admin|facility-dsd|facility-editor'])->group(function () {
    Route::get('/admin/facility/{facility}/dashboard', [\App\Http\Controllers\Admin\FacilityDashboardController::class, 'show'])->name('admin.facility.dashboard');

    // Facility Quick Actions
    Route::get('/admin/facility/{facility}/job-openings', [\App\Http\Controllers\Admin\JobOpeningController::class, 'index'])->name('admin.facility.job_openings');
    Route::post('/admin/facility/{facility}/job-openings', [\App\Http\Controllers\Admin\JobOpeningController::class, 'store'])->name('admin.facility.job_openings.store');
    Route::get('/admin/facility/{facility}/job-openings/{jobOpening}/edit', [\App\Http\Controllers\Admin\JobOpeningController::class, 'edit'])->name('admin.facility.job_openings.edit');
    Route::get('/admin/facility/{facility}/job-openings/{jobOpening}/data', [\App\Http\Controllers\Admin\JobOpeningController::class, 'getEditData'])->name('admin.facility.job_openings.data');
    Route::get('/admin/facility/{facility}/job-openings/{jobOpening}', [\App\Http\Controllers\Admin\JobOpeningController::class, 'show'])->name('admin.facility.job_openings.show');
    Route::put('/admin/facility/{facility}/job-openings/{jobOpening}', [\App\Http\Controllers\Admin\JobOpeningController::class, 'update'])->name('admin.facility.job_openings.update');
    Route::post('/admin/facility/{facility}/job-openings/{jobOpening}/update', [\App\Http\Controllers\Admin\JobOpeningController::class, 'updateViaForm'])->name('admin.facility.job_openings.update.form');
    Route::delete('/admin/facility/{facility}/job-openings/{jobOpening}', [\App\Http\Controllers\Admin\JobOpeningController::class, 'destroy'])->name('admin.facility.job_openings.destroy');
    Route::post('/admin/facility/{facility}/job-openings/toggle', [\App\Http\Controllers\Admin\JobOpeningController::class, 'toggleActive'])->name('admin.facility.job_openings.toggle');
    Route::post('/admin/facility/{facility}/job-openings/status', [\App\Http\Controllers\Admin\JobOpeningController::class, 'changeStatus'])->name('admin.facility.job_openings.status');
    Route::post('/admin/facility/{facility}/job-openings/delete', [\App\Http\Controllers\Admin\JobOpeningController::class, 'deleteJobOpening'])->name('admin.facility.job_openings.delete');
    Route::post('/admin/facility/{facility}/job-openings/template/delete', [\App\Http\Controllers\Admin\JobOpeningController::class, 'deleteTemplateViaForm'])->name('admin.facility.job_openings.template.delete');
    Route::post('/admin/facility/{facility}/job-openings/template/save', [\App\Http\Controllers\Admin\JobOpeningController::class, 'saveTemplateViaForm'])->name('admin.facility.job_openings.template.save');
    Route::post('/admin/facility/{facility}/job-openings/template/update', [\App\Http\Controllers\Admin\JobOpeningController::class, 'updateTemplateViaForm'])->name('admin.facility.job_openings.template.update');
    Route::get('/admin/facility/{facility}/hiring', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'hiring'])->name('admin.facility.hiring');
    Route::get('/admin/facility/{facility}/pre-employment/{application}', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'reviewPreEmployment'])->name('admin.facility.pre-employment.review');
    Route::match(['get', 'post'], '/admin/facility/{facility}/pre-employment/{application}/pdf', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'createPreEmploymentPdf'])->name('admin.facility.pre-employment.pdf');
    Route::post('/admin/facility/{facility}/pre-employment/{application}/status', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'updatePreEmploymentStatus'])->name('admin.facility.pre-employment.status');
    Route::get('/admin/facility/{facility}/document/{document}/download', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'downloadDocument'])->name('admin.facility.document.download');
    Route::get('/admin/facility/{facility}/document/{document}/view', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'viewDocument'])->name('admin.facility.document.view');
    Route::delete('/admin/facility/{facility}/document/{document}', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'deleteDocument'])->name('admin.facility.document.delete');
    Route::get('/admin/facility/{facility}/termination', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'termination'])->name('admin.facility.termination');
    Route::get('/admin/facility/{facility}/employees', [\App\Http\Controllers\Admin\EmployeesController::class, 'index'])->name('admin.facility.employees');
    // Route::get('/admin/facility/{facility}/attendance', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'attendance'])->name('admin.facility.attendance');
    Route::get('/admin/facility/{facility}/documents', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'documents'])->name('admin.facility.documents');
    Route::get('/admin/facility/{facility}/reports', [\App\Http\Controllers\Admin\Facilities\QuickActionsController::class, 'reports'])->name('admin.facility.reports');
});
// Root route: show landing page (welcome)
Route::get('/', function () {
    return redirect('/bio-pacific-corporate');
});

// Debug route - temporarily check URL generation
Route::get('/test-urls', function () {
    $code = 'TESTCODE';
    return [
        'login' => route('login'),
        'login_with_code' => route('login', ['code' => $code]),
        'register' => route('register'),
        'register_with_code' => route('register', ['code' => $code]),
        'pre_employment' => route('pre-employment.index'),
        'pre_employment_with_code' => route('pre-employment.index', ['code' => $code]),
    ];
});

// Services Management CRUD (Web Contents)
Route::middleware(['auth', 'role:admin|facility-admin|facility-dsd|hrrd'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('services', ServicesController::class);
});

Route::post('/webmaster/contact', [App\Http\Controllers\WebmasterController::class, 'submit'])->name('webmaster.contact.submit');

// Dynamic sitemap for all facilities
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);


// Admin Routes (auth + admin role)
Route::prefix('admin')->middleware(['auth', 'role:admin|facility-admin|facility-dsd|hrrd'])->name('admin.')->group(function () {
    // HR Portal Route removed from admin group; now only in HRRD-specific group above
    // Blog management (admin only, under web contents)
    Route::resource('blogs', BlogController::class)->names('blogs');
    // Admin dashboard route for compatibility with legacy references
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/facilities', [FacilityAdminController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [FacilityAdminController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [FacilityAdminController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility:slug}', [FacilityAdminController::class, 'show'])->name('facilities.show');
    Route::get('/facilities/{facility}/edit', [FacilityAdminController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{facility}', [FacilityAdminController::class, 'update'])->name('facilities.update');
    Route::post('/facilities/{facility}/services', [FacilityAdminController::class, 'updateServices'])->name('facilities.updateServices');

    // Add resource route for employees (show, edit, update)
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeesController::class)->only(['show', 'edit', 'update']);
    // Custom route for updating employee assignment (tabbed form)
    Route::put('employees/{employee}/update-assignment', [\App\Http\Controllers\Admin\EmployeesController::class, 'updateAssignment'])->name('employees.update_assignment');
    // Custom route for updating employee address (tabbed form)
    Route::put('employees/{employee}/update-address', [\App\Http\Controllers\Admin\EmployeesController::class, 'updateAddress'])->name('employees.address.update');
    // Custom route for updating employee personal profile (tabbed form)
    Route::put('employees/{employee}/update-personal', [\App\Http\Controllers\Admin\EmployeesController::class, 'updatePersonal'])->name('employees.personal.update');

    // Custom route for adding a phone to employee
    Route::post('employees/{employee}/phones/add', [\App\Http\Controllers\Admin\EmployeesController::class, 'addPhone'])->name('employees.phones.add');
    // Custom route for updating a phone for employee
    Route::put('employees/{employee}/phones/{phone}/update', [\App\Http\Controllers\Admin\EmployeesController::class, 'updatePhone'])->name('employees.phones.update');

    // Update only the user's email from modal
    Route::put('employees/{user}/update-email', [\App\Http\Controllers\Admin\EmployeesController::class, 'updateEmail'])
    ->name('employees.email.update');

    // System Settings page
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    // HIPAA Interactive route
    Route::get('/hipaa-checklist', function () {
        // TODO: Replace with actual controller if needed
        return view('admin.hipaa-checklist.index');
    })->name('hipaa-checklist.index');
    Route::get('/facilities/{facility}/hipaa-interactive', function (Facility $facility) {
        return view('admin.facilities.hipaa-interactive', compact('facility'));
    })->name('facilities.hipaa.interactive');
    
    // HIPAA Toggle route (admin version) - handle both ID and slug
    Route::post('/facilities/{facility}/hipaa/toggle', [FacilityController::class, 'toggleHipaaFlag'])->name('facilities.hipaa.toggle');
    
    // User Management CRUD
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    
    // Role Management CRUD
    Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [AdminRoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/roles/{role}/permissions', [AdminRoleController::class, 'getPermissions'])->name('roles.permissions');
    
    // Permission Management CRUD
    Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');

    // BAA Registry CRUD
    Route::get('/baa-registry', [BaaRegistryController::class, 'index'])->name('baa-registry.index');
    Route::get('/baa-registry/create', [BaaRegistryController::class, 'create'])->name('baa-registry.create');
    Route::post('/baa-registry', [BaaRegistryController::class, 'store'])->name('baa-registry.store');
    Route::get('/baa-registry/{vendor}/edit', [BaaRegistryController::class, 'edit'])->name('baa-registry.edit');
    Route::put('/baa-registry/{vendor}', [BaaRegistryController::class, 'update'])->name('baa-registry.update');
    Route::delete('/baa-registry/{vendor}', [BaaRegistryController::class, 'destroy'])->name('baa-registry.destroy');
    Route::get('/permissions/create', [AdminPermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [AdminPermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}', [AdminPermissionController::class, 'show'])->name('permissions.show');
    Route::get('/permissions/{permission}/edit', [AdminPermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [AdminPermissionController::class, 'destroy'])->name('permissions.destroy');
    Route::post('/permissions/bulk-assign', [AdminPermissionController::class, 'bulkAssign'])->name('permissions.bulk-assign');
    
    // Role Assignment Management
    Route::get('/role-assignments', [AdminRoleAssignmentController::class, 'index'])->name('role-assignments.index');
    Route::get('/role-assignments/{user}/edit', [AdminRoleAssignmentController::class, 'edit'])->name('role-assignments.edit');
    Route::put('/role-assignments/{user}', [AdminRoleAssignmentController::class, 'update'])->name('role-assignments.update');
    Route::post('/role-assignments/bulk-assign', [AdminRoleAssignmentController::class, 'bulkAssign'])->name('role-assignments.bulk-assign');
    Route::post('/role-assignments/quick-assign', [AdminRoleAssignmentController::class, 'quickAssign'])->name('role-assignments.quick-assign');
    Route::get('/role-assignments/statistics', [AdminRoleAssignmentController::class, 'statistics'])->name('role-assignments.statistics');
    Route::get('/role-assignments/{role}/users', [AdminRoleAssignmentController::class, 'getUsersForRole'])->name('role-assignments.users-for-role');
    
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
    
    // News management using Admin\NewsController
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->names('news');
    Route::delete('news/{news}/delete-image', [\App\Http\Controllers\Admin\NewsController::class, 'deleteImage'])->name('news.deleteImage');
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class)->names('events');

    // Positions Management CRUD
    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class)->names('positions');

    // Departments Management CRUD
    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->names('departments');

    // Email Recipients Management CRUD
    Route::resource('email-recipients', \App\Http\Controllers\Admin\EmailRecipientController::class)->names('email-recipients');

    // Employee Email Mappings Management
    Route::get('/communications/employee-email-mappings', function () {
        return view('admin.employee-email-mappings');
    })->middleware(['auth', 'role:admin|facility-admin|hrrd|facility-dsd'])->name('communications.employee-email-mappings');

    
    // Gallery image management
    Route::resource('galleries', GalleryController::class)->except(['show']);
    // Gallery index for a specific facility
    Route::get('/facilities/{facility}/galleries', [GalleryController::class, 'index'])->name('facilities.galleries.index');
    // Gallery image creation for a specific facility
    Route::get('/galleries/{facility}/create', [GalleryController::class, 'create'])->name('facilities.galleries.create');

    Route::get('/gallery/upload', [GalleryController::class, 'showUploadForm'])->name('gallery.upload');
    Route::post('/gallery/upload', [GalleryController::class, 'upload'])->name('gallery.upload.submit');
    // Route::post('/facilities/{facility}/gallery/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
    Route::delete('/gallery/{image}', [GalleryController::class, 'delete'])->name('gallery.delete');
    // Move gallery image up/down
    Route::post('/gallery/{image}/move/{direction}', [GalleryController::class, 'move'])->name('gallery.move');
    // Clear all gallery images for a facility
    Route::post('/facilities/{facility}/gallery/clear', [GalleryController::class, 'clearFacility'])->name('gallery.clear');

    // Admin Facility Testimonials Management
    Route::prefix('facilities')->middleware(['auth', 'role:admin|facility-admin|facility-dsd|hrrd'])->group(function () {
        Route::get('/{facility}/testimonials', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'index'])->name('admin.facilities.testimonials.index');
        Route::get('/{facility}/testimonials/create', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'create'])->name('admin.facilities.testimonials.create');
        Route::post('/{facility}/testimonials', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'store'])->name('admin.facilities.testimonials.store');
        Route::get('/{facility}/testimonials/{testimonial}/edit', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'edit'])->name('admin.facilities.testimonials.edit');
        Route::put('/{facility}/testimonials/{testimonial}', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'update'])->name('admin.facilities.testimonials.update');
        Route::delete('/{facility}/testimonials/{testimonial}', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'destroy'])->name('admin.facilities.testimonials.destroy');
    });
    Route::resource('tour-requests', \App\Http\Controllers\Admin\TourRequestController::class)->names('tour-requests');
    Route::resource('inquiries', InquiryController::class)->only(['index', 'show', 'destroy']);
    Route::resource('job-applications', AdminJobApplicationController::class)->only(['index', 'show', 'destroy'])->names('job-applications');

    // Ensure hrrd, facility-admin, facility-dsd, and admin roles have access to index route
    Route::middleware(['auth', 'role:admin|hrrd|facility-admin|facility-dsd'])->group(function () {
       Route::get('/admin/job-openings', [\App\Http\Controllers\Admin\JobOpeningController::class, 'index'])->name('job-openings.index');
    });
    Route::patch('/job-applications/{jobApplication}/status', [AdminJobApplicationController::class, 'updateStatus'])->name('job-applications.update-status');
    Route::get('/job-applications/{jobApplication}/download-resume', [AdminJobApplicationController::class, 'downloadResume'])->name('job-applications.download-resume');
    Route::get('/job-applications/{jobApplication}/preview-resume', [AdminJobApplicationController::class, 'previewResume'])->name('job-applications.preview-resume');
    
    // View user dashboard (for authorized staff)
    Route::get('/users/{user}/dashboard', [DashboardController::class, 'showUserDashboard'])->name('users.dashboard');

    // API endpoint for fetching a single testimonial (for edit modal)
    Route::get('/facilities/web-contents/testimonials/{testimonial}', [\App\Http\Controllers\Admin\FacilityTestimonialController::class, 'show'])->middleware(['auth', 'role:admin|facility-admin|facility-dsd|hrrd'])->name('admin.facilities.testimonials.show');

    // Security Monitoring Routes
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'index'])->name('dashboard');
        Route::get('/anomalies', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'anomalies'])->name('anomalies');
        Route::get('/record-logs/{tokenType}/{recordId}', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'recordLogs'])->name('record-logs');
        Route::get('/incidents', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'incidents'])->name('incidents');
        Route::get('/export', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'exportReport'])->name('export');
        Route::get('/cleanup', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'cleanup'])->name('cleanup');
        Route::post('/cleanup', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'performCleanup'])->name('cleanup.perform');
        
        // AJAX endpoints for incident management
        Route::post('/anomalies/{id}/investigated', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'markAsInvestigated'])->name('anomalies.investigated');
        Route::post('/incidents/{id}/resolve', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'resolveIncident'])->name('incidents.resolve');
        Route::post('/incidents/{id}/review', [\App\Http\Controllers\Admin\SecurityMonitoringController::class, 'markUnderReview'])->name('incidents.review');
    });

});

// Email Templates Management (admin + facility-admin + facility-dsd + hrrd)
Route::prefix('admin')->middleware(['auth', 'role:admin|facility-admin|facility-dsd|hrrd'])
    ->name('admin.')
    ->group(function () {
        Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplateController::class)->names('email-templates');
        Route::post('email-templates/{emailTemplate}/send-reply', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'sendReply'])
            ->name('email-templates.send-reply');
    });

// Facility-specific admin routes for facility-admin and facility-editor roles
Route::prefix('admin')->middleware(['auth', 'role:facility-admin|facility-editor|admin'])
    ->name('admin.')
    ->group(function () {
        // Route::get('/facility/{facility}/dashboard', [FacilityAdminController::class, 'dashboard'])->name('facility.dashboard'); // Removed to avoid conflict; handled by FacilityDashboardController
        Route::get('/facility/content', [FacilityAdminController::class, 'facilityContent'])->name('facility.content');
        Route::get('/facility/users', [AdminUserController::class, 'facilityUsers'])->name('facility.users');
        Route::get('/facility/settings', [FacilityAdminController::class, 'facilitySettings'])->name('facility.settings');
        // Shortcut: /admin/facility/{facility} redirects to dashboard
        Route::get('/facility/{facility}', [FacilityAdminController::class, 'dashboard'])->name('facility.dashboard.shortcut');
    });

// Careers CRUD and applications routes
Route::prefix('admin/facilities/webcontents')->middleware(['auth', 'role:admin|facility-admin|hrrd|facility-dsd'])->group(function () {
    Route::get('careers/templates', [CareersController::class, 'templates'])->name('admin.facilities.webcontents.careers.templates');
    Route::get('careers', [CareersController::class, 'indexAll'])->name('admin.facilities.webcontents.careers');
    Route::get('careers/{facility}', [CareersController::class, 'index'])->name('admin.facilities.webcontents.careers.show');
    Route::post('careers/{facility}', [CareersController::class, 'store'])->name('admin.facilities.webcontents.careers.store');
    Route::put('careers/{jobOpening}', [CareersController::class, 'update'])->name('admin.facilities.webcontents.careers.update');
    Route::delete('careers/{jobOpening}', [CareersController::class, 'destroy'])->name('admin.facilities.webcontents.careers.destroy');
    Route::get('careers/{jobOpening}/applications', [CareersController::class, 'applications'])->name('admin.facilities.webcontents.careers.applications');
    Route::put('careers/{jobOpening}/applications/{jobApplication}', [CareersController::class, 'updateApplication'])->name('admin.facilities.webcontents.careers.applications.update');
    Route::delete('careers/{jobOpening}/applications/{jobApplication}', [CareersController::class, 'destroyApplication'])->name('admin.facilities.webcontents.careers.applications.destroy');
    Route::get('careers/applications/{jobApplication}/details', [CareersController::class, 'applicationDetails'])->name('admin.facilities.webcontents.careers.applications.details');
    Route::get('careers/{jobOpening}/applications/{jobApplication}/download-resume', [CareersController::class, 'downloadResume'])->name('admin.facilities.webcontents.careers.applications.download-resume');
    Route::get('careers/{jobOpening}/applications/{jobApplication}/preview-resume', [CareersController::class, 'previewResume'])->name('admin.facilities.webcontents.careers.applications.preview-resume');
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


use App\Http\Controllers\HomeController;
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');

Route::post('/careers/apply', [CareersPublicController::class, 'apply'])->name('careers.apply');
Route::post('/book-a-tour', [BookATourController::class, 'store'])->name('book-a-tour.store');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Auth routes (must be before catch-all routes)
require __DIR__.'/auth.php';

// Explicit admin login routes (GET and POST)
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store']);
});

// Job applications viewing route
Route::get('/applications/{id}', [JobApplicationController::class, 'show'])->name('applications.show');

// List job applications for a facility
Route::get('/facilities/{facility}/applications', [CareersApplicationsController::class, 'index'])->name('facilities.applications.index');

// Place Livewire update routes at the very end to avoid being shadowed by catch-all routes
Route::post('/livewire/update', [HandleRequests::class, 'handleUpdate'])
    ->middleware(['web'])
    ->name('livewire.update');
Route::post('/livewire/update', [HandleRequests::class, 'handleUpdate'])
    ->middleware(['web'])
    ->name('default.livewire.update');
Route::post('/admin/{any}/livewire/update', [HandleRequests::class, 'handleUpdate'])
    ->middleware(['auth', 'role:admin|hrrd|facility-admin|facility-dsd'])
    ->where('any', '.*')
    ->name('livewire.update.admin');

// Register admin webmaster contacts routes
require __DIR__.'/admin_webmaster_contacts.php';
// Register admin incident contacts routes
require __DIR__.'/admin_incident_contacts.php';

// Secure Inquiry Routes
Route::get('/secure/inquiry/{token}', [App\Http\Controllers\SecureInquiryController::class, 'view'])
    ->name('secure.inquiry.view');
Route::post('/secure/inquiry/{token}/verify-staff', [App\Http\Controllers\SecureInquiryController::class, 'verifyStaff'])
    ->name('secure.inquiry.verify-staff');
    
// Secure Job Application Routes  
Route::get('/secure/job-application/{token}', [App\Http\Controllers\SecureJobApplicationController::class, 'show'])
    ->name('secure.job-application');
Route::post('/secure/job-application/{token}/verify-staff', [App\Http\Controllers\SecureJobApplicationController::class, 'verifyStaff'])
    ->name('secure.job-application.verify-staff');
Route::get('/secure/job-application/{token}/download-resume', [App\Http\Controllers\SecureJobApplicationController::class, 'downloadResume'])
    ->name('secure.job-application.download-resume');
    
// Secure Tour Request Routes
Route::get('/secure/tour-request/{token}', [App\Http\Controllers\SecureTourRequestController::class, 'view'])
    ->name('secure.tour-request');
Route::post('/secure/tour-request/{token}/verify-staff', [App\Http\Controllers\SecureTourRequestController::class, 'verifyStaff'])
    ->name('secure.verify-staff');
Route::post('/secure/tour-request/{token}/log-access', [App\Http\Controllers\SecureTourRequestController::class, 'logAccess'])
    ->name('secure.tour-request.log-access');
    
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/secure-inquiries', [App\Http\Controllers\SecureInquiryController::class, 'adminIndex'])
        ->name('admin.secure-inquiries.index');
    Route::post('/admin/secure-inquiries/{inquiry}/regenerate-token', [App\Http\Controllers\SecureInquiryController::class, 'regenerateToken'])
        ->name('admin.secure-inquiries.regenerate-token');
        
    Route::get('/admin/secure-tour-requests', [App\Http\Controllers\SecureTourRequestController::class, 'adminIndex'])
        ->name('admin.secure-tour-requests.index');
    Route::post('/admin/secure-tour-requests/{tourRequest}/regenerate-token', [App\Http\Controllers\SecureTourRequestController::class, 'regenerateToken'])
        ->name('admin.secure-tour-requests.regenerate-token');
});

// MFA routes for admin
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/mfa', [AdminMfaController::class, 'showMfaForm'])->name('admin.mfa.form');
    Route::post('/admin/mfa', [AdminMfaController::class, 'verifyMfa'])->name('admin.mfa.verify');
});

// MFA setup routes for admin (web guard)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/mfa/setup', [App\Http\Controllers\Auth\AdminMfaSetupController::class, 'showSetupForm'])->name('admin.mfa.setup.form');
    Route::post('/admin/mfa/setup', [App\Http\Controllers\Auth\AdminMfaSetupController::class, 'storeSetup'])->name('admin.mfa.setup.store');
});


// Public facility routes (placed at the end)
    Route::get('/{facility:slug}/webmaster/contact', [App\Http\Controllers\WebmasterController::class, 'show'])->name('webmaster.contact.show');
    Route::post('/{facility:slug}/webmaster/contact', [App\Http\Controllers\WebmasterController::class, 'submit'])->name('webmaster.contact.facility.submit');

// Redirect old privacy-policy route to new one
Route::get('/{facility:slug}/privacy-policy', function (Facility $facility) {
    return redirect()->route('privacy.policy', ['facility' => $facility->slug]);
});

// Publicly accessible Privacy Policy route
Route::get('/{facility:slug}/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('privacy.policy');

// Notice of Privacy Practices
Route::get('/{facility:slug}/notice-of-privacy-practices', [NoticeOfPrivacyPracticesController::class, 'show'])->name('notice.privacy.practices');

// Terms of Service
Route::get('/{facility:slug}/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.service');

// Accessibility
Route::get('/{facility:slug}/accessibility', [AccessibilityController::class, 'show'])->name('accessibility');

// Public Facility Route (catch-all, must be last)

// Redirect /home to /bio-pacific-corporate
Route::get('/home', function() {
    return redirect('/bio-pacific-corporate');
})->name('home');

// Redirect /{facility}/admin/dashboard to facility dashboard if user has access
Route::get('/{facility:slug}/admin/dashboard', function ($facilitySlug) {
    $facility = \App\Models\Facility::where('slug', $facilitySlug)->firstOrFail();
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user && ($user->hasRole('facility-admin') || $user->hasRole('admin') || $user->hasRole('hrrd') || $user->hasRole('facility-dsd') || $user->hasRole('facility-editor'))) {
        // Optionally check if user is assigned to this facility
        return redirect()->route('admin.facility.dashboard', ['facility' => $facility->slug]);
    }
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('facility.admin.dashboard.redirect');

// Pre-Employment Registration Route (must come before catch-all facility routes)
Route::get('/pre-employment/{code?}', [PreEmploymentController::class, 'show'])->name('pre-employment.index');


// Pre-Employment Portal (authenticated users)
Route::get('/my-pre-employment', [PreEmploymentController::class, 'portal'])
    ->middleware(['auth'])
    ->name('pre-employment.portal');

// Employment Portal (authenticated users)
Route::get('/my-employment', [\App\Http\Controllers\EmploymentController::class, 'portal'])
    ->middleware(['auth'])
    ->name('employment.portal');

Route::post('/my-pre-employment/checklist/{employeeChecklist}', [\App\Http\Controllers\PreEmploymentChecklistController::class, 'update'])
    ->middleware(['auth'])
    ->name('pre-employment.checklist.update');

Route::post('/my-pre-employment/checklist/{employeeChecklist}/return', [\App\Http\Controllers\PreEmploymentChecklistController::class, 'returnForEdit'])
    ->middleware(['auth', 'role:admin|facility-admin|hrrd'])
    ->name('pre-employment.checklist.return');

Route::post('/my-pre-employment/checklist/{employeeChecklist}/approve', [\App\Http\Controllers\PreEmploymentChecklistController::class, 'approve'])
    ->middleware(['auth', 'role:admin|facility-admin|hrrd'])
    ->name('pre-employment.checklist.approve');

// Employee Application Form
Route::post('/my-pre-employment/employee-application', [EmployeeApplicationController::class, 'store'])
    ->middleware(['auth'])
    ->name('employee-application.store');

// Hiring Actions (Admin/HRRD only)
Route::middleware(['auth', 'role:admin|hrrd|facility-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/pre-employment/{preEmployment}/hire', [HireApplicantController::class, 'hire'])
        ->name('pre-employment.hire');
    Route::post('/pre-employment/{preEmployment}/reject', [HireApplicantController::class, 'reject'])
        ->name('pre-employment.reject');
});

// Redirect /{facility}/dashboard to facility dashboard if user has access
Route::get('/{facility:slug}/dashboard', function ($facilitySlug) {
    $facility = \App\Models\Facility::where('slug', $facilitySlug)->firstOrFail();
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user && ($user->hasRole('facility-admin') || $user->hasRole('admin') || $user->hasRole('hrrd') || $user->hasRole('facility-dsd') || $user->hasRole('facility-editor'))) {
        // You may want to check if the user is assigned to this facility
        // Example: if ($user->facility_id == $facility->id) { ... }
        return redirect()->route('admin.facility.dashboard', ['facility' => $facility->slug]);
    }
    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('facility.dashboard.redirect');

// Facility public page by slug (e.g. /almaden-healthcare-and-rehabilitation-center)
Route::get('/{facility:slug}', [FacilityController::class, 'publicView'])->name('facility.public');

// Minimal Livewire validation test route
Route::get('/admin/facilities/test_livewire_validation', function() {
    return view('admin.facilities.test_livewire_validation');
});