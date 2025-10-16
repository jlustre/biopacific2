@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Testimonials Management</h2>
                        <p class="text-sm text-gray-600">Select a facility to view and manage its testimonials</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Facility Selection -->
        @include('admin.facilities.webcontents.partials.facility_dropdown', ['facilities' => $facilities])

        <!-- Testimonials Content Area -->
        <div id="testimonialsContent" class="hidden">
            <!-- Selected Facility Info -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-building text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 id="selectedFacilityName" class="text-lg font-semibold text-blue-900"></h3>
                            <p id="selectedFacilityLocation" class="text-sm text-blue-700"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonials List -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Facility Testimonials</h3>
                        <div class="flex items-center space-x-4">
                            <button id="addTestimonialBtn"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center shadow-sm">
                                <i class="fas fa-plus mr-2"></i>Add New Testimonial
                            </button>
                            <div class="text-sm text-gray-500">
                                Total: <span id="testimonialCount" class="font-semibold">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="testimonialsList" class="divide-y divide-gray-200">
                    <!-- Testimonials will be loaded here -->
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-quote-right text-4xl text-gray-300 mb-4"></i>
                        <p>No testimonials found for this facility.</p>
                        <p class="text-sm mt-2">Click "Add New Testimonial" to create the first one.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Default State (No Facility Selected) -->
        <div id="defaultState" class="bg-white rounded-lg shadow p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 mb-4">
                    <i class="fas fa-quote-right text-primary text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Testimonials Management</h3>
                <p class="text-gray-500 mb-6">Select a facility from the dropdown above to manage its testimonials. This
                    page will allow you to:</p>
                <ul class="text-sm text-gray-600 space-y-2 max-w-md mx-auto text-left">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        View and manage existing testimonials
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Add new facility-specific testimonials
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Organize testimonials by rating and featured status
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Control testimonial visibility
                    </li>
                </ul>
            </div>
        </div>
        <!-- Add/Edit Testimonial Modal -->
        <div id="addTestimonialModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden z-50 p-4">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0"
                    id="modalContent">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-teal-600 to-teal-700 text-white px-8 py-6 rounded-t-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-quote-right text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold">Add New Testimonial</h3>
                                    <p class="text-teal-100 text-sm">Share a positive experience about our facility</p>
                                </div>
                            </div>
                            <button id="closeModalBtn"
                                class="w-8 h-8 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all duration-200">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Modal Body -->
                    <form id="testimonialForm" class="p-8" enctype="multipart/form-data">
                        <input type="hidden" id="modalFacilityId" name="facility_id">
                        <input type="hidden" id="testimonialId" name="testimonial_id" value="">
                        <input type="hidden" id="isEditMode" name="is_edit" value="false">
                        <!-- Author Information Section -->
                        <div class="mb-8">
                            <div class="flex items-center mb-6">
                                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-teal-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">Author Information</h4>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label for="authorName"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Author Name <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="authorName" name="name" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400"
                                        placeholder="Enter the full name of the person giving the testimonial">
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                    <div>
                                        <label for="authorTitle"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Author Title</label>
                                        <div class="relative">
                                            <select id="authorTitle" name="title"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 bg-white appearance-none cursor-pointer">
                                                <option value="">Select title...</option>
                                                <option value="Mr.">Mr.</option>
                                                <option value="Mrs.">Mrs.</option>
                                                <option value="Ms.">Ms.</option>
                                                <option value="Miss">Miss</option>
                                                <option value="Dr.">Dr.</option>
                                            </select>
                                            <div
                                                class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="relationship"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Relationship to
                                            Facility</label>
                                        <div class="relative">
                                            <select id="relationship" name="relationship"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 bg-white appearance-none cursor-pointer">
                                                <option value="">Select relationship...</option>
                                                <option value="Current Patient">Current Patient</option>
                                                <option value="Former Patient">Former Patient</option>
                                                <option value="Patient Family Member">Patient Family Member</option>
                                                <option value="Visitor">Visitor</option>
                                                <option value="Current Staff">Current Staff</option>
                                                <option value="Former Staff">Former Staff</option>
                                                <option value="Healthcare Professional">Healthcare Professional</option>
                                                <option value="Volunteer">Volunteer</option>
                                                <option value="Community Member">Community Member</option>
                                                <option value="Business Partner">Business Partner</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <div
                                                class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Testimonial Content Section -->
                        <div class="mb-8">
                            <div class="flex items-center mb-6">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-star text-yellow-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">Testimonial Details</h4>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label for="testimonialTitleHeader"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Testimonial
                                        Title/Header</label>
                                    <input type="text" id="testimonialTitleHeader" name="title_header"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400"
                                        placeholder="e.g. A Journey of Healing, Exceptional Care Experience">
                                    <p class="text-xs text-gray-500 mt-2">Optional: Short headline for this testimonial
                                    </p>
                                </div>
                                <div>
                                    <label for="rating"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Rating</label>
                                    <div class="relative">
                                        <select id="rating" name="rating"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 bg-white appearance-none cursor-pointer">
                                            <option value="5">⭐⭐⭐⭐⭐ 5 Stars - Excellent</option>
                                            <option value="4">⭐⭐⭐⭐☆ 4 Stars - Very Good</option>
                                            <option value="3">⭐⭐⭐☆☆ 3 Stars - Good</option>
                                            <option value="2">⭐⭐☆☆☆ 2 Stars - Fair</option>
                                            <option value="1">⭐☆☆☆☆ 1 Star - Poor</option>
                                        </select>
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="testimonialText"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Testimonial Content <span
                                            class="text-gray-400 text-sm">(Short Version)</span><span
                                            class="text-red-500">*</span></label>
                                    <textarea id="testimonialText" name="quote" rows="6" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400 resize-none"
                                        placeholder="Write the testimonial content here. Share the positive experience and what made our facility special..."></textarea>
                                    <p class="text-xs text-gray-500 mt-2">Minimum 20 characters recommended for a
                                        meaningful testimonial</p>
                                </div>
                                <div>
                                    <label for="testimonialStory"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Testimonial Story <span
                                            class="text-gray-400 text-sm">(Full story)</span></label>
                                    <textarea id="testimonialStory" name="story" rows="4"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400 resize-none"
                                        placeholder="Share a longer story or details about the experience..."></textarea>
                                    <p class="text-xs text-gray-500 mt-2">Optional: Add more details about the
                                        testimonial experience</p>
                                </div>
                            </div>
                        </div>
                        <!-- Options Section -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-cog text-purple-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">Display Options</h4>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label
                                        class="flex items-center p-3 bg-white rounded-lg border-2 border-gray-200 hover:border-teal-300 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" id="isActive" name="is_active" checked
                                            class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 focus:ring-2">
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-700">Active</div>
                                            <div class="text-xs text-gray-500">Visible on website</div>
                                        </div>
                                    </label>
                                    <label
                                        class="flex items-center p-3 bg-white rounded-lg border-2 border-gray-200 hover:border-yellow-300 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" id="isFeatured" name="is_featured"
                                            class="w-5 h-5 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 focus:ring-2">
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-700">Featured</div>
                                            <div class="text-xs text-gray-500">Highlighted testimonial</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <!-- Avatar Upload Section (moved below checkboxes) -->
                            <div class="mt-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-image text-blue-600"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-800">Avatar Photo</h4>
                                </div>
                                <div class="flex items-center space-x-6">
                                    <div>
                                        <div id="defaultAvatar"
                                            class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center border">
                                            <i class="fas fa-user text-gray-400 text-3xl"></i>
                                        </div>
                                        <img id="photoPreview" src="" alt="Avatar Preview"
                                            class="w-16 h-16 rounded-full object-cover border hidden">
                                    </div>
                                    <div>
                                        <input type="file" id="photo" name="photo" accept="image/*"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <input type="hidden" id="currentPhotoUrl" name="photo_url" value="">
                                        <p class="text-xs text-gray-500 mt-2">Upload a square image for best results.
                                            Max size: 2MB.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Footer -->
                        <div class="bg-gray-50 px-8 py-6 rounded-b-2xl border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    All fields marked with <span class="text-red-500">*</span> are required
                                </div>
                                <div class="flex space-x-3">
                                    <button type="button" id="cancelModalBtn"
                                        class="px-6 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 hover:border-gray-400 font-semibold transition-all duration-200 flex items-center space-x-2">
                                        <i class="fas fa-times"></i>
                                        <span>Cancel</span>
                                    </button>
                                    <button type="submit" form="testimonialForm" id="submitButton"
                                        class="px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2">
                                        <i class="fas fa-plus" id="submitIcon"></i>
                                        <span id="submitText">Add Testimonial</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const facilitySelect = document.getElementById('facilitySelect');
    const testimonialsContent = document.getElementById('testimonialsContent');
    const defaultState = document.getElementById('defaultState');
    const testimonialCount = document.getElementById('testimonialCount');
    const testimonialsList = document.getElementById('testimonialsList');
    let currentFacilityId = null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Photo preview logic
    document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const defaultAvatar = document.getElementById('defaultAvatar');
    const currentPhotoUrlInput = document.getElementById('currentPhotoUrl');
    if (photoInput) {
    photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
    const reader = new FileReader();
    reader.onload = function(evt) {
    photoPreview.src = evt.target.result;
    photoPreview.classList.remove('hidden');
    defaultAvatar.classList.add('hidden');
    };
    reader.readAsDataURL(file);
    } else {
    // If no new file, show last uploaded photo if available, else icon
    const currentPhotoUrl = currentPhotoUrlInput.value;
    if (currentPhotoUrl) {
    photoPreview.src = currentPhotoUrl;
    photoPreview.classList.remove('hidden');
    defaultAvatar.classList.add('hidden');
    } else {
    photoPreview.src = '';
    photoPreview.classList.add('hidden');
    defaultAvatar.classList.remove('hidden');
    }
    }
    });
                        }
                        });

    // Load testimonials from API
    async function loadTestimonials(facilityId) {
        try {
            testimonialsList.innerHTML = '<div class="p-6 text-center"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="mt-2 text-gray-500">Loading testimonials...</p></div>';
            const response = await fetch(`/admin/facilities/web-contents/testimonials/${facilityId}/data`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const result = await response.json();
            if (result.success && result.testimonials) {
                testimonialCount.textContent = result.count;
                renderTestimonials(result.testimonials);
            } else {
                testimonialsList.innerHTML = `<div class=\"p-6 text-center text-gray-500\"><i class=\"fas fa-quote-right text-4xl text-gray-300 mb-4\"></i><p>No testimonials found for this facility.</p><p class=\"text-sm mt-2\">Click \"Add New Testimonial\" to create the first one.</p></div>`;
                testimonialCount.textContent = '0';
            }
        } catch (error) {
            testimonialsList.innerHTML = `<div class=\"p-6 text-center text-red-500\"><i class=\"fas fa-exclamation-triangle text-2xl mb-2\"></i><p>Error loading testimonials</p></div>`;
            testimonialCount.textContent = '0';
        }
    }

    // Render testimonials
    function renderTestimonials(testimonials) {
        if (!testimonials || testimonials.length === 0) {
            testimonialsList.innerHTML = `<div class=\"p-6 text-center text-gray-500\"><i class=\"fas fa-quote-right text-4xl text-gray-300 mb-4\"></i><p>No testimonials found for this facility.</p><p class=\"text-sm mt-2\">Click \"Add New Testimonial\" to create the first one.</p></div>`;
            return;
        }
        testimonialsList.innerHTML = testimonials.map(testimonial => {
            const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
            const featuredBadge = testimonial.is_featured ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2"><i class="fas fa-star mr-1"></i>Featured</span>' : '';
            const statusBadge = testimonial.is_active ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">Active</span>' : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">Inactive</span>';
            let photoHtml = '';
            if (testimonial.photo_url) {
                photoHtml = `<img src=\"${testimonial.photo_url}\" alt=\"Photo\" class=\"w-12 h-12 rounded-full object-cover border mr-4\">`;
            } else {
                photoHtml = `<svg class=\"w-12 h-12 text-gray-300 mr-4\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z\" /></svg>`;
            }
            return `<div class=\"p-6 hover:bg-gray-50 transition-colors\"><div class=\"flex items-start justify-between\"><div class=\"flex items-start flex-1\">${photoHtml}<div class=\"flex-1\"><div class=\"flex items-center mb-2\"><h4 class=\"text-lg font-semibold text-gray-900\">${testimonial.name}</h4>${testimonial.title_header ? `<span class='ml-2 text-primary font-bold text-base'>${testimonial.title_header}</span>` : ''}${featuredBadge}${statusBadge}</div><div class=\"mb-2\"><div class=\"flex items-center text-sm text-gray-600\">${testimonial.title ? `<span class=\"font-medium\">${testimonial.title}</span>` : ''}${testimonial.title && testimonial.relationship ? '<span class=\"mx-2\">•</span>' : ''}${testimonial.relationship ? `<span>${testimonial.relationship}</span>` : ''}</div><div class=\"flex items-center mt-1\"><span class=\"text-yellow-400 mr-2\">${stars}</span><span class=\"text-sm text-gray-500\">(${testimonial.rating}/5)</span></div></div><blockquote class=\"text-gray-700 italic border-l-4 border-primary pl-4 py-2\">\"${testimonial.quote}\"</blockquote>${testimonial.story ? `<div class='mt-2 text-gray-600 text-sm'>${testimonial.story}</div>` : ''}<div class=\"mt-3 text-xs text-gray-500\">Created: ${new Date(testimonial.created_at).toLocaleDateString()}${testimonial.updated_at !== testimonial.created_at ? `• Updated: ${new Date(testimonial.updated_at).toLocaleDateString()}` : ''}</div></div></div><div class=\"flex items-center space-x-2 ml-4\"><button class=\"text-blue-600 hover:text-blue-800 p-2\" title=\"Edit\"><i class=\"fas fa-edit\"></i></button><button class=\"text-red-600 hover:text-red-800 p-2\" title=\"Delete\"><i class=\"fas fa-trash\"></i></button></div></div></div>`;
        }).join('');
    }

    // Facility selection event
    facilitySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        currentFacilityId = this.value;
        if (this.value) {
            testimonialsContent.classList.remove('hidden');
            defaultState.classList.add('hidden');
            document.getElementById('selectedFacilityName').textContent = selectedOption.dataset.name;
            document.getElementById('selectedFacilityLocation').textContent = `${selectedOption.dataset.city || 'N/A'}, ${selectedOption.dataset.state || 'N/A'}`;
            loadTestimonials(this.value);
        } else {
            currentFacilityId = null;
            testimonialsContent.classList.add('hidden');
            defaultState.classList.remove('hidden');
        }
    });
</script>
@endpush

@push('scripts')
<script>
    // ...existing code...

    // Utility function for alerts
    function showAlert(type, message) {
        const alertTypes = {
            success: { bg: 'bg-green-100', text: 'text-green-800', icon: 'fas fa-check-circle' },
            error: { bg: 'bg-red-100', text: 'text-red-800', icon: 'fas fa-exclamation-circle' },
            info: { bg: 'bg-blue-100', text: 'text-blue-800', icon: 'fas fa-info-circle' }
        };
        const config = alertTypes[type] || alertTypes.info;
        const alert = document.createElement('div');
        alert.className = `fixed top-4 right-4 z-50 ${config.bg} ${config.text} px-4 py-3 rounded-lg shadow-lg max-w-sm`;
        alert.innerHTML = `<div class="flex items-center"><i class="${config.icon} mr-2"></i><span>${message}</span></div>`;
        document.body.appendChild(alert);
        setTimeout(() => { alert.remove(); }, 4000);
    }

    // Add/Edit Modal logic (simple version)
    let modal = null;
    let modalContent = null;
    let form = null;
    document.addEventListener('DOMContentLoaded', function() {
        modal = document.getElementById('addTestimonialModal');
        modalContent = modal ? document.getElementById('modalContent') : null;
        form = document.getElementById('testimonialForm');
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }
        const addBtn = document.getElementById('addTestimonialBtn');
        if (addBtn) addBtn.addEventListener('click', openAddModal);
    });

    function openAddModal() {
        if (!modal) return;
        form.reset();
        document.getElementById('modalFacilityId').value = currentFacilityId;
        document.getElementById('testimonialId').value = '';
        document.getElementById('isEditMode').value = 'false';
        // Clear avatar preview and hidden input
        const currentPhotoUrlInput = document.getElementById('currentPhotoUrl');
        const photoPreview = document.getElementById('photoPreview');
        const defaultAvatar = document.getElementById('defaultAvatar');
        if (currentPhotoUrlInput && photoPreview && defaultAvatar) {
            currentPhotoUrlInput.value = '';
            photoPreview.src = '';
            photoPreview.classList.add('hidden');
            defaultAvatar.classList.remove('hidden');
        }
        showModal('Add New Testimonial', 'Share a positive experience about our facility');
    }

    function showModal(title, subtitle) {
        if (!modal || !modalContent) return;
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.querySelector('#addTestimonialModal h3').textContent = title;
        document.querySelector('#addTestimonialModal p.text-teal-100').textContent = subtitle;
        setTimeout(() => {
            document.getElementById('authorName').focus();
        }, 200);
    }

    function closeModal() {
        if (!modal || !modalContent) return;
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            form.reset();
        }, 300);
    }

    // Attach close/cancel events
    document.addEventListener('DOMContentLoaded', function() {
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
        if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    });

    // Form submit handler (add/edit)
    async function handleFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData(form);
        formData.set('is_active', document.getElementById('isActive').checked ? '1' : '0');
        formData.set('is_featured', document.getElementById('isFeatured').checked ? '1' : '0');
        const isEdit = formData.get('is_edit') === 'true';
        const testimonialId = formData.get('testimonial_id');
        let url, method;
        if (isEdit) {
            url = `/admin/facilities/web-contents/testimonials/${testimonialId}`;
            method = 'POST';
            formData.append('_method', 'PUT');
        } else {
            url = '{{ route("admin.facilities.webcontents.testimonials.store") }}';
            method = 'POST';
        }
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                showAlert('success', `Testimonial ${isEdit ? 'updated' : 'created'} successfully!`);
                closeModal();
                if (currentFacilityId) loadTestimonials(currentFacilityId);
            } else {
                showAlert('error', result.message || `Error ${isEdit ? 'updating' : 'creating'} testimonial`);
            }
        } catch (error) {
            showAlert('error', `Network error occurred while ${isEdit ? 'updating' : 'creating'} testimonial`);
        }
    }

    // Edit and Delete functions

    window.editTestimonial = async function(testimonialId) {
        try {
            const response = await fetch(`/admin/facilities/web-contents/testimonials/${testimonialId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await response.json();
            if (data.success) {
                // Populate modal fields
                form.reset();
                document.getElementById('modalFacilityId').value = data.testimonial.facility_id;
                document.getElementById('testimonialId').value = data.testimonial.id;
                document.getElementById('isEditMode').value = 'true';
                document.getElementById('authorName').value = data.testimonial.name || '';
                document.getElementById('authorTitle').value = data.testimonial.title || '';
                document.getElementById('relationship').value = data.testimonial.relationship || '';
                document.getElementById('testimonialText').value = data.testimonial.quote || '';
                document.getElementById('rating').value = data.testimonial.rating || 5;
                document.getElementById('isActive').checked = data.testimonial.is_active == 1;
                document.getElementById('isFeatured').checked = data.testimonial.is_featured == 1;
                document.getElementById('testimonialTitleHeader').value = data.testimonial.title_header || '';
                document.getElementById('testimonialStory').value = data.testimonial.story || '';
                // Set avatar preview and hidden input
                const currentPhotoUrlInput = document.getElementById('currentPhotoUrl');
                const photoPreview = document.getElementById('photoPreview');
                const defaultAvatar = document.getElementById('defaultAvatar');
                if (currentPhotoUrlInput && photoPreview && defaultAvatar) {
                    if (data.testimonial.photo_url) {
                        currentPhotoUrlInput.value = data.testimonial.photo_url;
                        photoPreview.src = data.testimonial.photo_url;
                        photoPreview.classList.remove('hidden');
                        defaultAvatar.classList.add('hidden');
                    } else {
                        currentPhotoUrlInput.value = '';
                        photoPreview.src = '';
                        photoPreview.classList.add('hidden');
                        defaultAvatar.classList.remove('hidden');
                    }
                }
                // Update modal title and button for edit mode
                showModal('Edit Testimonial', 'Update the testimonial information');
                const submitIcon = document.getElementById('submitIcon');
                const submitText = document.getElementById('submitText');
                if (submitIcon && submitText) {
                    submitIcon.className = 'fas fa-save';
                    submitText.textContent = 'Update Testimonial';
                }
            } else {
                showAlert('error', 'Failed to load testimonial data');
            }
        } catch (error) {
            showAlert('error', `Network error occurred while loading testimonial: ${error.message}`);
        }
    }

    window.deleteTestimonial = async function(testimonialId, testimonialName) {
        if (!confirm(`Are you sure you want to delete the testimonial from "${testimonialName}"? This action cannot be undone.`)) return;
        try {
            const response = await fetch(`/admin/facilities/web-contents/testimonials/${testimonialId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                showAlert('success', result.message);
                if (currentFacilityId) loadTestimonials(currentFacilityId);
            } else {
                showAlert('error', result.message || 'Error deleting testimonial');
            }
        } catch (error) {
            showAlert('error', 'Network error occurred while deleting testimonial');
        }
    }

    // Update renderTestimonials to attach handlers
    function renderTestimonials(testimonials) {
        if (!testimonials || testimonials.length === 0) {
            testimonialsList.innerHTML = `<div class=\"p-6 text-center text-gray-500\"><i class=\"fas fa-quote-right text-4xl text-gray-300 mb-4\"></i><p>No testimonials found for this facility.</p><p class=\"text-sm mt-2\">Click \"Add New Testimonial\" to create the first one.</p></div>`;
            return;
        }
        testimonialsList.innerHTML = testimonials.map(testimonial => {
            const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
            const featuredBadge = testimonial.is_featured ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2"><i class="fas fa-star mr-1"></i>Featured</span>' : '';
            const statusBadge = testimonial.is_active ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">Active</span>' : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">Inactive</span>';
            let photoHtml = '';
            if (testimonial.photo_url) {
                photoHtml = `<img src=\"${testimonial.photo_url}\" alt=\"Photo\" class=\"w-12 h-12 rounded-full object-cover border mr-4\">`;
            } else {
                photoHtml = `<svg class=\"w-12 h-12 text-gray-300 mr-4\" fill=\"currentColor\" viewBox=\"0 0 24 24\"><path d=\"M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z\" /></svg>`;
            }
            return `<div class=\"p-6 hover:bg-gray-50 transition-colors\"><div class=\"flex items-start justify-between\"><div class=\"flex items-start flex-1\">${photoHtml}<div class=\"flex-1\"><div class=\"flex items-center mb-2\"><h4 class=\"text-lg font-semibold text-gray-900\">${testimonial.name}</h4>${testimonial.title_header ? `<span class='ml-2 text-primary font-bold text-base'>${testimonial.title_header}</span>` : ''}${featuredBadge}${statusBadge}</div><div class=\"mb-2\"><div class=\"flex items-center text-sm text-gray-600\">${testimonial.title ? `<span class=\"font-medium\">${testimonial.title}</span>` : ''}${testimonial.title && testimonial.relationship ? '<span class=\"mx-2\">•</span>' : ''}${testimonial.relationship ? `<span>${testimonial.relationship}</span>` : ''}</div><div class=\"flex items-center mt-1\"><span class=\"text-yellow-400 mr-2\">${stars}</span><span class=\"text-sm text-gray-500\">(${testimonial.rating}/5)</span></div></div><blockquote class=\"text-gray-700 italic border-l-4 border-primary pl-4 py-2\">\"${testimonial.quote}\"</blockquote>${testimonial.story ? `<div class='mt-2 text-gray-600 text-sm'>${testimonial.story}</div>` : ''}<div class=\"mt-3 text-xs text-gray-500\">Created: ${new Date(testimonial.created_at).toLocaleDateString()}${testimonial.updated_at !== testimonial.created_at ? `• Updated: ${new Date(testimonial.updated_at).toLocaleDateString()}` : ''}</div></div></div><div class=\"flex items-center space-x-2 ml-4\"><button onclick=\"editTestimonial(${testimonial.id})\" class=\"text-blue-600 hover:text-blue-800 p-2\" title=\"Edit\"><i class=\"fas fa-edit\"></i></button><button onclick=\"deleteTestimonial(${testimonial.id}, '${testimonial.name.replace(/'/g, "\\'")}')\" class=\"text-red-600 hover:text-red-800 p-2\" title=\"Delete\"><i class=\"fas fa-trash\"></i></button></div></div></div>`;
        }).join('');
    }
</script>
@endpush