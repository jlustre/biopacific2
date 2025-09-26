@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <!-- Facility Selection Dropdown -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Testimonials Management</h2>
                    <p class="text-sm text-gray-600">Select a facility to view and manage its testimonials</p>
                </div>
            </div>

            <!-- Facility Dropdown -->
            <div class="mb-6">
                <label for="facilitySelect" class="block text-sm font-semibold text-gray-700 mb-3">Select
                    Facility:</label>
                <div class="relative w-full max-w-md">
                    <select id="facilitySelect" name="facility_id"
                        class="w-full pl-12 pr-12 py-4 border-2 border-gray-200 rounded-xl bg-white text-gray-700 font-medium focus:ring-3 focus:ring-teal-200 focus:border-teal-500 hover:border-gray-300 transition-all duration-200 appearance-none cursor-pointer shadow-sm text-sm sm:text-base">
                        <option value="" class="text-gray-500">Choose a facility...</option>
                        @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" data-name="{{ $facility->name }}"
                            data-city="{{ $facility->city }}" data-state="{{ $facility->state }}"
                            class="text-gray-700 py-2">
                            {{ $facility->name }} - {{ $facility->city ?? 'N/A' }}, {{ $facility->state ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                    <!-- Right Arrow Icon -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400 transition-colors duration-200" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                        </svg>
                    </div>
                    <!-- Left Building Icon -->
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="fas fa-building text-gray-400 text-sm"></i>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 max-w-md">Select a facility to view and manage its testimonials</p>
            </div>
        </div>

        <!-- Testimonials Content Area -->
        <div id="testimonialsContent" class="hidden">
            <!-- Selected Facility Info -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
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
                        Add new testimonials
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Feature testimonials on facility pages
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Organize testimonials by category
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Add Testimonial Modal -->
    <div id="addTestimonialModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden z-50 p-4">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0"
                id="modalContent">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-teal-600 to-teal-700 text-white px-8 py-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
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
                <form id="testimonialForm" class="p-8">
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
                            <!-- Author Name -->
                            <div>
                                <label for="authorName" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Author Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="authorName" name="name" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400"
                                    placeholder="Enter the full name of the person giving the testimonial">
                            </div>

                            <!-- Two Column Layout -->
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
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
                            <!-- Rating -->
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
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Testimonial Content -->
                            <div>
                                <label for="testimonialText" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Testimonial Content <span class="text-red-500">*</span>
                                </label>
                                <textarea id="testimonialText" name="quote" rows="6" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400 resize-none"
                                    placeholder="Write the testimonial content here. Share the positive experience and what made our facility special..."></textarea>
                                <p class="text-xs text-gray-500 mt-2">Minimum 20 characters recommended for a meaningful
                                    testimonial</p>
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
                    </div>
                </form>

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
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const facilitySelect = document.getElementById('facilitySelect');
    const testimonialsContent = document.getElementById('testimonialsContent');
    const defaultState = document.getElementById('defaultState');
    const addTestimonialBtn = document.getElementById('addTestimonialBtn');
    const modal = document.getElementById('addTestimonialModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const form = document.getElementById('testimonialForm');
    
    let currentFacilityId = null;
    let currentTestimonials = [];
    
    // CSRF Token for API calls
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    // Handle facility selection
    facilitySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        currentFacilityId = this.value;
        
        if (this.value) {
            // Show testimonials content
            testimonialsContent.classList.remove('hidden');
            defaultState.classList.add('hidden');
            
            // Update selected facility info
            document.getElementById('selectedFacilityName').textContent = selectedOption.dataset.name;
            document.getElementById('selectedFacilityLocation').textContent = 
                `${selectedOption.dataset.city || 'N/A'}, ${selectedOption.dataset.state || 'N/A'}`;
            
            // Load testimonials for this facility
            loadTestimonials(this.value);
        } else {
            // Show default state
            currentFacilityId = null;
            testimonialsContent.classList.add('hidden');
            defaultState.classList.remove('hidden');
        }
    });
    
    // Modal handlers with animations
    addTestimonialBtn.addEventListener('click', function() {
        if (currentFacilityId) {
            document.getElementById('modalFacilityId').value = currentFacilityId;
            openModal();
        }
    });
    
    closeModalBtn.addEventListener('click', () => closeModal());
    cancelModalBtn.addEventListener('click', () => closeModal());
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Enhanced modal functions with animations
    function showModal() {
        modal.classList.remove('hidden');
        const modalContent = document.getElementById('modalContent');
        
        // Trigger animation
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        // Focus first input
        setTimeout(() => {
            document.getElementById('authorName').focus();
        }, 200);
    }
    
    function openModal() {
        // Reset form for add mode
        form.reset();
        document.getElementById('testimonialId').value = '';
        document.getElementById('isEditMode').value = 'false';
        document.getElementById('modalFacilityId').value = currentFacilityId;
        document.getElementById('isActive').checked = true;
        
        // Reset modal title
        const modalTitle = document.querySelector('#addTestimonialModal h3');
        const modalSubtitle = document.querySelector('#addTestimonialModal p.text-teal-100');
        modalTitle.textContent = 'Add New Testimonial';
        modalSubtitle.textContent = 'Share a positive experience about our facility';
        
        // Reset submit button for add mode
        const submitIcon = document.getElementById('submitIcon');
        const submitText = document.getElementById('submitText');
        if (submitIcon && submitText) {
            submitIcon.className = 'fas fa-plus';
            submitText.textContent = 'Add Testimonial';
        }
        
        // Reset rating display
        updateRatingDisplay(5);
        document.getElementById('rating').value = 5;
        
        showModal();
    }
    
    function closeModal() {
        const modalContent = document.getElementById('modalContent');
        
        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        // Hide modal after animation
        setTimeout(() => {
            modal.classList.add('hidden');
            form.reset();
            document.getElementById('isActive').checked = true;
            // Reset edit mode fields
            document.getElementById('testimonialId').value = '';
            document.getElementById('isEditMode').value = 'false';
            
            // Reset submit button to add mode
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');
            if (submitIcon && submitText) {
                submitIcon.className = 'fas fa-plus';
                submitText.textContent = 'Add Testimonial';
            }
        }, 300);
    }
    
    // Form submission with API integration
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEdit = formData.get('is_edit') === 'true';
        const testimonialId = formData.get('testimonial_id');
        
        const data = {
            facility_id: parseInt(formData.get('facility_id')),
            name: formData.get('name'),
            title: formData.get('title') || null,
            relationship: formData.get('relationship') || null,
            quote: formData.get('quote'),
            rating: parseInt(formData.get('rating')),
            is_active: formData.get('is_active') ? true : false,
            is_featured: formData.get('is_featured') ? true : false
        };
        
        try {
            let url, method;
            if (isEdit) {
                url = `/admin/facilities/web-contents/testimonials/${testimonialId}`;
                method = 'PUT';
            } else {
                url = '{{ route("admin.facilities.webcontents.testimonials.store") }}';
                method = 'POST';
            }
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                const actionText = isEdit ? 'updated' : 'created';
                showAlert('success', `Testimonial ${actionText} successfully!`);
                closeModal();
                
                // Reload testimonials for current facility
                if (currentFacilityId) {
                    loadTestimonials(currentFacilityId);
                }
            } else {
                showAlert('error', result.message || `Error ${isEdit ? 'updating' : 'creating'} testimonial`);
            }
        } catch (error) {
            console.error('Error:', error);
            const actionText = isEdit ? 'updating' : 'creating';
            showAlert('error', `Network error occurred while ${actionText} testimonial`);
        }
    });
    
    // Load testimonials from API
    async function loadTestimonials(facilityId) {
        try {
            document.getElementById('testimonialsList').innerHTML = '<div class="p-6 text-center"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="mt-2 text-gray-500">Loading testimonials...</p></div>';
            
            const response = await fetch(`/admin/facilities/web-contents/testimonials/${facilityId}/data`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                currentTestimonials = result.testimonials;
                document.getElementById('testimonialCount').textContent = result.count;
                renderTestimonials(result.testimonials);
            } else {
                throw new Error(result.message || 'Failed to load testimonials');
            }
        } catch (error) {
            console.error('Error loading testimonials:', error);
            document.getElementById('testimonialsList').innerHTML = `
                <div class="p-6 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>Error loading testimonials</p>
                    <p class="text-sm mt-1">${error.message}</p>
                </div>
            `;
        }
    }
    
    // Render testimonials list
    function renderTestimonials(testimonials) {
        const container = document.getElementById('testimonialsList');
        
        if (testimonials.length === 0) {
            container.innerHTML = `
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-quote-right text-4xl text-gray-300 mb-4"></i>
                    <p>No testimonials found for this facility.</p>
                    <p class="text-sm mt-2">Click "Add New Testimonial" to create the first one.</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = testimonials.map(testimonial => {
            const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
            const featuredBadge = testimonial.is_featured ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2"><i class="fas fa-star mr-1"></i>Featured</span>' : '';
            const statusBadge = testimonial.is_active ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">Active</span>' : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">Inactive</span>';
            
            return `
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h4 class="text-lg font-semibold text-gray-900">${testimonial.name}</h4>
                                ${featuredBadge}
                                ${statusBadge}
                            </div>
                            
                            <div class="mb-2">
                                <div class="flex items-center text-sm text-gray-600">
                                    ${testimonial.title ? `<span class="font-medium">${testimonial.title}</span>` : ''}
                                    ${testimonial.title && testimonial.relationship ? '<span class="mx-2">•</span>' : ''}
                                    ${testimonial.relationship ? `<span>${testimonial.relationship}</span>` : ''}
                                </div>
                                <div class="flex items-center mt-1">
                                    <span class="text-yellow-400 mr-2">${stars}</span>
                                    <span class="text-sm text-gray-500">(${testimonial.rating}/5)</span>
                                </div>
                            </div>
                            
                            <blockquote class="text-gray-700 italic border-l-4 border-primary pl-4 py-2">
                                "${testimonial.quote}"
                            </blockquote>
                            
                            <div class="mt-3 text-xs text-gray-500">
                                Created: ${new Date(testimonial.created_at).toLocaleDateString()}
                                ${testimonial.updated_at !== testimonial.created_at ? `• Updated: ${new Date(testimonial.updated_at).toLocaleDateString()}` : ''}
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2 ml-4">
                            <button onclick="editTestimonial(${testimonial.id})" class="text-blue-600 hover:text-blue-800 p-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTestimonial(${testimonial.id}, '${testimonial.name}')" class="text-red-600 hover:text-red-800 p-2" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Delete testimonial
    window.deleteTestimonial = async function(testimonialId, testimonialName) {
        if (!confirm(`Are you sure you want to delete the testimonial from "${testimonialName}"? This action cannot be undone.`)) {
            return;
        }
        
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
                if (currentFacilityId) {
                    loadTestimonials(currentFacilityId);
                }
            } else {
                showAlert('error', result.message || 'Error deleting testimonial');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Network error occurred while deleting testimonial');
        }
    };
    
    // Edit testimonial function
    window.editTestimonial = async function(testimonialId) {
        try {
            console.log('Fetching testimonial with ID:', testimonialId);
            
            // Get testimonial data
            const response = await fetch(`/admin/facilities/web-contents/testimonials/${testimonialId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                console.log('Testimonial data received:', data.testimonial);
                
                // Update modal title first
                const modalTitle = document.querySelector('#addTestimonialModal h3');
                const modalSubtitle = document.querySelector('#addTestimonialModal p.text-teal-100');
                if (modalTitle && modalSubtitle) {
                    modalTitle.textContent = 'Edit Testimonial';
                    modalSubtitle.textContent = 'Update the testimonial information';
                }
                
                // Update submit button for edit mode
                const submitIcon = document.getElementById('submitIcon');
                const submitText = document.getElementById('submitText');
                if (submitIcon && submitText) {
                    submitIcon.className = 'fas fa-save';
                    submitText.textContent = 'Update Testimonial';
                }
                
                // Show modal first
                showModal();
                
                // Wait a bit for modal to be fully rendered before populating
                setTimeout(() => {
                    populateEditForm(data.testimonial);
                }, 100);
            } else {
                showAlert('error', 'Failed to load testimonial data');
            }
        } catch (error) {
            console.error('Error details:', error);
            showAlert('error', `Network error occurred while loading testimonial: ${error.message}`);
        }
    };
    
    // Populate form with testimonial data for editing
    function populateEditForm(testimonial) {
        console.log('Populating form with testimonial:', testimonial);
        
        try {
            // Set hidden fields
            const testimonialIdField = document.getElementById('testimonialId');
            const isEditModeField = document.getElementById('isEditMode');
            const modalFacilityIdField = document.getElementById('modalFacilityId');
            
            if (!testimonialIdField) {
                throw new Error('testimonialId field not found');
            }
            if (!isEditModeField) {
                throw new Error('isEditMode field not found');
            }
            if (!modalFacilityIdField) {
                throw new Error('modalFacilityId field not found');
            }
            
            testimonialIdField.value = testimonial.id;
            isEditModeField.value = 'true';
            modalFacilityIdField.value = testimonial.facility_id;
            
            // Populate form fields
            const authorNameField = document.getElementById('authorName');
            const authorTitleField = document.getElementById('authorTitle');
            const relationshipField = document.getElementById('relationship');
            const quoteField = document.getElementById('testimonialText');
            const ratingField = document.getElementById('rating');
            const isActiveField = document.getElementById('isActive');
            const isFeaturedField = document.getElementById('isFeatured');
            
            if (!authorNameField) {
                throw new Error('authorName field not found');
            }
            if (!authorTitleField) {
                throw new Error('authorTitle field not found');
            }
            if (!relationshipField) {
                throw new Error('relationship field not found');
            }
            if (!quoteField) {
                throw new Error('testimonialText field not found');
            }
            if (!ratingField) {
                throw new Error('rating field not found');
            }
            if (!isActiveField) {
                throw new Error('isActive field not found');
            }
            if (!isFeaturedField) {
                throw new Error('isFeatured field not found');
            }
            
            authorNameField.value = testimonial.name || '';
            authorTitleField.value = testimonial.title || '';
            relationshipField.value = testimonial.relationship || '';
            quoteField.value = testimonial.quote || '';
            ratingField.value = testimonial.rating || 5;
            isActiveField.checked = testimonial.is_active == 1;
            isFeaturedField.checked = testimonial.is_featured == 1;
            
            // Update rating stars display
            updateRatingDisplay(testimonial.rating || 5);
            
            console.log('Form populated successfully');
        } catch (error) {
            console.error('Error populating form:', error);
            showAlert('error', `Error populating form: ${error.message}`);
        }
    }
    
    // Helper function to update rating stars display
    function updateRatingDisplay(rating) {
        const stars = document.querySelectorAll('.rating-star');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
    
    // Utility functions
    function showAlert(type, message) {
        const alertTypes = {
            success: { bg: 'bg-green-100', text: 'text-green-800', icon: 'fas fa-check-circle' },
            error: { bg: 'bg-red-100', text: 'text-red-800', icon: 'fas fa-exclamation-circle' },
            info: { bg: 'bg-blue-100', text: 'text-blue-800', icon: 'fas fa-info-circle' }
        };
        
        const config = alertTypes[type] || alertTypes.info;
        
        const alert = document.createElement('div');
        alert.className = `fixed top-4 right-4 z-50 ${config.bg} ${config.text} px-4 py-3 rounded-lg shadow-lg max-w-sm`;
        alert.innerHTML = `
            <div class="flex items-center">
                <i class="${config.icon} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
});
</script>
@endsection