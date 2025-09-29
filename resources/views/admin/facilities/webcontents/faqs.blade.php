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
                        <h1 class="text-3xl font-bold text-gray-900">FAQs Management</h1>
                        <p class="text-gray-600">Manage frequently asked questions for your facilities</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Facility Selection -->
        <div class="mb-8 bg-white rounded-lg shadow-sm p-6">
            <!-- Facility Dropdown -->
            <div class="mb-6">
                <label for="facilitySelect" class="block text-sm font-semibold text-gray-700 mb-3">Select
                    Facility:</label>
                <div class="relative w-full max-w-md">
                    <select id="facilitySelect" name="facility_id"
                        class="w-full pl-12 pr-12 py-4 border-2 border-gray-200 rounded-xl bg-white text-gray-700 font-medium focus:ring-3 focus:ring-teal-200 focus:border-teal-500 hover:border-gray-300 transition-all duration-200 appearance-none cursor-pointer shadow-sm text-sm sm:text-base">
                        <option value="">Choose a facility...</option>
                        @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" data-name="{{ $facility->name }}"
                            data-city="{{ $facility->city }}" data-state="{{ $facility->state }}"
                            data-phone="{{ $facility->phone }}">
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
                <p class="mt-2 text-xs text-gray-500 max-w-md">Select a facility to view and manage its FAQs</p>
            </div>
        </div>

        <!-- FAQs Content Area -->
        <div id="faqsContent" class="hidden">
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
                    <div class="text-right">
                        <div class="flex items-center text-blue-600">
                            <i class="fas fa-phone text-sm mr-2"></i>
                            <span id="selectedFacilityPhone" class="text-sm font-medium"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQs List -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Facility FAQs</h3>
                        <div class="flex items-center space-x-4">
                            <button id="addFaqBtn"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center shadow-sm">
                                <i class="fas fa-plus mr-2"></i>Add New FAQ
                            </button>
                            <div class="text-sm text-gray-500">
                                Total: <span id="faqCount" class="font-semibold">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="faqsList" class="divide-y divide-gray-200">
                    <!-- FAQs will be loaded here -->
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-question-circle text-4xl text-gray-300 mb-4"></i>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-4 text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="currentColor" />
                            <text x="12" y="16" text-anchor="middle" font-size="14" fill="white"
                                font-family="Arial, sans-serif">?</text>
                        </svg>
                        <p>No FAQs found for this facility.</p>
                        <p class="text-sm mt-2">Click "Add New FAQ" to create the first one.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Default State (No Facility Selected) -->
        <div id="defaultState" class="bg-white rounded-lg shadow p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 mb-4">
                    <i class="fas fa-question-circle text-primary text-xl"></i>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="currentColor" />
                        <text x="12" y="16" text-anchor="middle" font-size="14" fill="white"
                            font-family="Arial, sans-serif">?</text>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">FAQs Management</h3>
                <p class="text-gray-500 mb-6">Select a facility from the dropdown above to manage its FAQs. This
                    page will allow you to:</p>
                <ul class="text-sm text-gray-600 space-y-2 max-w-md mx-auto text-left">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        View and manage existing FAQs
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Add new facility-specific FAQs
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Use default FAQs shared across facilities
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Organize FAQs by categories
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Control FAQ visibility and priority
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Add/Edit FAQ Modal -->
    <div id="addFaqModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden z-50 p-4">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0"
                id="modalContent">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-teal-600 to-teal-700 text-white px-8 py-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-question-circle text-lg"></i>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"
                                        fill="currentColor" />
                                    <text x="12" y="16" text-anchor="middle" font-size="14" fill="white"
                                        font-family="Arial, sans-serif">?</text>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Add New FAQ</h3>
                                <p class="text-teal-100 text-sm">Create a frequently asked question</p>
                            </div>
                        </div>
                        <button id="closeModalBtn"
                            class="w-8 h-8 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all duration-200">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <form id="faqForm" class="p-8">
                    <input type="hidden" id="modalFacilityId" name="facility_id">
                    <input type="hidden" id="faqId" name="faq_id" value="">
                    <input type="hidden" id="isEditMode" name="is_edit" value="false">

                    <!-- FAQ Information Section -->
                    <div class="mb-8">
                        <div class="flex items-center mb-6">
                            <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-question text-teal-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">FAQ Details</h4>
                        </div>

                        <div class="space-y-5">
                            <!-- Question -->
                            <div>
                                <label for="faqQuestion" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Question <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="faqQuestion" name="question" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400"
                                    placeholder="Enter the frequently asked question">
                            </div>

                            <!-- Two Column Layout -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                <div>
                                    <label for="faqCategory"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                    <div class="relative">
                                        <select id="faqCategory" name="category"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 bg-white appearance-none cursor-pointer">
                                            <option value="">Select category...</option>
                                            <option value="General Information">General Information</option>
                                            <option value="Admission">Admission</option>
                                            <option value="Insurance & Billing">Insurance & Billing</option>
                                            <option value="Care Services">Care Services</option>
                                            <option value="Amenities">Amenities</option>
                                            <option value="Activities">Activities</option>
                                            <option value="Dining">Dining</option>
                                            <option value="Health & Safety">Health & Safety</option>
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
                                    <label for="faqIcon" class="block text-sm font-semibold text-gray-700 mb-2">Icon
                                        Class</label>
                                    <input type="text" id="faqIcon" name="icon"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400"
                                        placeholder="e.g., fas fa-question-circle">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Answer Section -->
                    <div class="mb-8">
                        <div class="flex items-center mb-6">
                            <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-comment text-teal-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Answer</h4>
                        </div>

                        <div>
                            <label for="faqAnswer" class="block text-sm font-semibold text-gray-700 mb-2">Answer <span
                                    class="text-red-500">*</span></label>
                            <textarea id="faqAnswer" name="answer" rows="6" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700 placeholder-gray-400 resize-vertical"
                                placeholder="Provide a detailed answer to the question..."></textarea>
                        </div>
                    </div>

                    <!-- Settings Section -->
                    <div class="mb-8">
                        <div class="flex items-center mb-6">
                            <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-cog text-teal-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">FAQ Settings</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Active Status -->
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                                <div class="flex-shrink-0">
                                    <input type="checkbox" id="isActive" name="is_active" checked
                                        class="w-5 h-5 text-teal-600 border-2 border-gray-300 rounded focus:ring-teal-500 focus:ring-offset-0">
                                </div>
                                <div class="flex-1">
                                    <label for="isActive" class="text-sm font-semibold text-gray-700">Active FAQ</label>
                                    <p class="text-xs text-gray-500">FAQ will be visible on the website</p>
                                </div>
                            </div>

                            <!-- Featured Status -->
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                                <div class="flex-shrink-0">
                                    <input type="checkbox" id="isFeatured" name="is_featured"
                                        class="w-5 h-5 text-teal-600 border-2 border-gray-300 rounded focus:ring-teal-500 focus:ring-offset-0">
                                </div>
                                <div class="flex-1">
                                    <label for="isFeatured" class="text-sm font-semibold text-gray-700">Featured
                                        FAQ</label>
                                    <p class="text-xs text-gray-500">Highlight this FAQ prominently</p>
                                </div>
                            </div>

                            <!-- Default FAQ -->
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                                <div class="flex-shrink-0">
                                    <input type="checkbox" id="isDefault" name="is_default"
                                        class="w-5 h-5 text-teal-600 border-2 border-gray-300 rounded focus:ring-teal-500 focus:ring-offset-0">
                                </div>
                                <div class="flex-1">
                                    <label for="isDefault" class="text-sm font-semibold text-gray-700">Default
                                        FAQ</label>
                                    <p class="text-xs text-gray-500">Available to all facilities</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="mt-5">
                            <label for="sortOrder" class="block text-sm font-semibold text-gray-700 mb-2">Display
                                Order</label>
                            <input type="number" id="sortOrder" name="sort_order" min="0" value="0"
                                class="w-32 px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-3 focus:ring-teal-200 focus:border-teal-500 transition-all duration-200 text-gray-700">
                            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-gray-50 -mx-8 -mb-8 px-8 py-6 rounded-b-2xl">
                        <div class="flex justify-end space-x-4">
                            <button type="button" id="cancelBtn"
                                class="px-6 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 hover:border-gray-400 font-semibold transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-times"></i>
                                <span>Cancel</span>
                            </button>
                            <button type="submit" form="faqForm" id="submitButton"
                                class="px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center space-x-2">
                                <i class="fas fa-plus" id="submitIcon"></i>
                                <span id="submitText">Add FAQ</span>
                            </button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    const modal = document.getElementById('addFaqModal');
    const form = document.getElementById('faqForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let currentFacilityId = null;

    // Facility selection handling
    document.getElementById('facilitySelect').addEventListener('change', function() {
        const facilityId = this.value;
        currentFacilityId = facilityId;
        
        if (facilityId) {
            const selectedOption = this.options[this.selectedIndex];
            const facilityName = selectedOption.getAttribute('data-name');
            const facilityCity = selectedOption.getAttribute('data-city');
            const facilityState = selectedOption.getAttribute('data-state');
            const facilityPhone = selectedOption.getAttribute('data-phone');
            
            document.getElementById('selectedFacilityName').textContent = facilityName;
            document.getElementById('selectedFacilityLocation').textContent = `${facilityCity}, ${facilityState}`;
            
            // Format and display phone number
            const phoneElement = document.getElementById('selectedFacilityPhone');
            if (facilityPhone && facilityPhone.trim() !== '') {
                // Format phone number
                const digits = facilityPhone.replace(/\D/g, '');
                let formattedPhone = facilityPhone;
                if (digits.length === 10) {
                    formattedPhone = `(${digits.substr(0,3)}) ${digits.substr(3,3)}-${digits.substr(6)}`;
                }
                phoneElement.textContent = formattedPhone;
                phoneElement.parentElement.style.display = 'flex';
            } else {
                phoneElement.parentElement.style.display = 'none';
            }
            
            document.getElementById('defaultState').classList.add('hidden');
            document.getElementById('faqsContent').classList.remove('hidden');
            
            loadFaqs(facilityId);
        } else {
            document.getElementById('defaultState').classList.remove('hidden');
            document.getElementById('faqsContent').classList.add('hidden');
        }
    });

    // Modal event listeners
    document.getElementById('addFaqBtn').addEventListener('click', openModal);
    document.getElementById('closeModalBtn').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    
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
            document.getElementById('faqQuestion').focus();
        }, 200);
    }
    
    function openModal() {
        // Reset form for add mode
        form.reset();
        document.getElementById('faqId').value = '';
        document.getElementById('isEditMode').value = 'false';
        document.getElementById('modalFacilityId').value = currentFacilityId;
        document.getElementById('isActive').checked = true;
        
        // Reset modal title
        const modalTitle = document.querySelector('#addFaqModal h3');
        const modalSubtitle = document.querySelector('#addFaqModal p.text-teal-100');
        modalTitle.textContent = 'Add New FAQ';
        modalSubtitle.textContent = 'Create a frequently asked question';
        
        // Reset submit button for add mode
        const submitIcon = document.getElementById('submitIcon');
        const submitText = document.getElementById('submitText');
        if (submitIcon && submitText) {
            submitIcon.className = 'fas fa-plus';
            submitText.textContent = 'Add FAQ';
        }
        
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
            document.getElementById('faqId').value = '';
            document.getElementById('isEditMode').value = 'false';
            
            // Reset submit button to add mode
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');
            if (submitIcon && submitText) {
                submitIcon.className = 'fas fa-plus';
                submitText.textContent = 'Add FAQ';
            }
        }, 300);
    }

    // Form submission with API integration
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEdit = formData.get('is_edit') === 'true';
        const faqId = formData.get('faq_id');
        
        const data = {
            facility_id: parseInt(formData.get('facility_id')) || null,
            question: formData.get('question'),
            answer: formData.get('answer'),
            category: formData.get('category') || null,
            icon: formData.get('icon') || null,
            is_active: formData.get('is_active') ? true : false,
            is_featured: formData.get('is_featured') ? true : false,
            is_default: formData.get('is_default') ? true : false,
            sort_order: parseInt(formData.get('sort_order')) || 0
        };
        
        try {
            let url, method;
            if (isEdit) {
                url = `/admin/facilities/web-contents/faqs/${faqId}`;
                method = 'PUT';
            } else {
                url = '{{ route("admin.facilities.webcontents.faqs.store") }}';
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
                showAlert('success', `FAQ ${actionText} successfully!`);
                closeModal();
                
                // Reload FAQs for current facility
                if (currentFacilityId) {
                    loadFaqs(currentFacilityId);
                }
            } else {
                showAlert('error', result.message || `Error ${isEdit ? 'updating' : 'creating'} FAQ`);
            }
        } catch (error) {
            console.error('Error:', error);
            const actionText = isEdit ? 'updating' : 'creating';
            showAlert('error', `Network error occurred while ${actionText} FAQ`);
        }
    });

    // Load FAQs from API
    async function loadFaqs(facilityId) {
        try {
            document.getElementById('faqsList').innerHTML = '<div class="p-6 text-center"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="mt-2 text-gray-500">Loading FAQs...</p></div>';
            
            const response = await fetch(`/admin/facilities/web-contents/faqs/${facilityId}/data`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.faqs) {
                renderFaqs(data.faqs);
                document.getElementById('faqCount').textContent = data.count;
            } else {
                document.getElementById('faqsList').innerHTML = '<div class="p-6 text-center text-gray-500"><i class="fas fa-question-circle text-4xl text-gray-300 mb-4"></i><p>No FAQs found for this facility.</p></div>';
                document.getElementById('faqCount').textContent = '0';
            }
        } catch (error) {
            console.error('Error loading FAQs:', error);
            document.getElementById('faqsList').innerHTML = '<div class="p-6 text-center text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error loading FAQs</p></div>';
        }
    }

    // Render FAQs in the list
    function renderFaqs(faqs) {
        if (!faqs || faqs.length === 0) {
            document.getElementById('faqsList').innerHTML = '<div class="p-6 text-center text-gray-500"><i class="fas fa-question-circle text-4xl text-gray-300 mb-4"></i><p>No FAQs found for this facility.</p><p class="text-sm mt-2">Click "Add New FAQ" to create the first one.</p></div>';
            return;
        }

        const faqsHtml = faqs.map(faq => `
            <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0 pr-4">
                        <div class="flex items-center space-x-3 mb-3">
                            ${faq.icon ? `<i class="${faq.icon} text-teal-600 text-lg"></i>` : '<i class="fas fa-question-circle text-teal-600 text-lg"></i>'}
                            <div class="flex items-center space-x-2">
                                <h4 class="text-lg font-semibold text-gray-900 leading-tight">${faq.question}</h4>
                                ${faq.is_featured ? '<span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Featured</span>' : ''}
                                ${faq.is_default ? '<span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Default</span>' : ''}
                                ${!faq.is_active ? '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>' : ''}
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed mb-3">${faq.answer.substring(0, 200)}${faq.answer.length > 200 ? '...' : ''}</p>
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            ${faq.category ? `<span class="flex items-center"><i class="fas fa-tag mr-1"></i>${faq.category}</span>` : ''}
                            <span class="flex items-center"><i class="fas fa-sort-numeric-down mr-1"></i>Order: ${faq.sort_order}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-4">
                        <button onclick="editFaq(${faq.id})" class="text-blue-600 hover:text-blue-800 p-2" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteFaq(${faq.id}, '${faq.question.replace(/'/g, "\\'")}', event)" class="text-red-600 hover:text-red-800 p-2" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        document.getElementById('faqsList').innerHTML = faqsHtml;
    }

    // Delete FAQ function
    window.deleteFaq = async function(faqId, faqQuestion, event) {
        event.stopPropagation();
        
        if (!confirm(`Are you sure you want to delete the FAQ: "${faqQuestion}"?`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/facilities/web-contents/faqs/${faqId}`, {
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
                    loadFaqs(currentFacilityId);
                }
            } else {
                showAlert('error', result.message || 'Error deleting FAQ');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Network error occurred while deleting FAQ');
        }
    };

    // Edit FAQ function
    window.editFaq = async function(faqId) {
        try {
            console.log('Fetching FAQ with ID:', faqId);
            
            // Get FAQ data
            const response = await fetch(`/admin/facilities/web-contents/faqs/${faqId}`, {
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
                console.log('FAQ data received:', data.faq);
                
                // Update modal title first
                const modalTitle = document.querySelector('#addFaqModal h3');
                const modalSubtitle = document.querySelector('#addFaqModal p.text-teal-100');
                if (modalTitle && modalSubtitle) {
                    modalTitle.textContent = 'Edit FAQ';
                    modalSubtitle.textContent = 'Update the FAQ information';
                }
                
                // Update submit button for edit mode
                const submitIcon = document.getElementById('submitIcon');
                const submitText = document.getElementById('submitText');
                if (submitIcon && submitText) {
                    submitIcon.className = 'fas fa-save';
                    submitText.textContent = 'Update FAQ';
                }
                
                // Show modal first
                showModal();
                
                // Wait a bit for modal to be fully rendered before populating
                setTimeout(() => {
                    populateEditForm(data.faq);
                }, 100);
            } else {
                showAlert('error', 'Failed to load FAQ data');
            }
        } catch (error) {
            console.error('Error details:', error);
            showAlert('error', `Network error occurred while loading FAQ: ${error.message}`);
        }
    };

    // Populate form with FAQ data for editing
    function populateEditForm(faq) {
        console.log('Populating form with FAQ:', faq);
        
        try {
            // Set hidden fields
            const faqIdField = document.getElementById('faqId');
            const isEditModeField = document.getElementById('isEditMode');
            const modalFacilityIdField = document.getElementById('modalFacilityId');
            
            if (!faqIdField) {
                throw new Error('faqId field not found');
            }
            if (!isEditModeField) {
                throw new Error('isEditMode field not found');
            }
            if (!modalFacilityIdField) {
                throw new Error('modalFacilityId field not found');
            }
            
            faqIdField.value = faq.id;
            isEditModeField.value = 'true';
            modalFacilityIdField.value = faq.facility_id || '';
            
            // Populate form fields
            const questionField = document.getElementById('faqQuestion');
            const answerField = document.getElementById('faqAnswer');
            const categoryField = document.getElementById('faqCategory');
            const iconField = document.getElementById('faqIcon');
            const sortOrderField = document.getElementById('sortOrder');
            const isActiveField = document.getElementById('isActive');
            const isFeaturedField = document.getElementById('isFeatured');
            const isDefaultField = document.getElementById('isDefault');
            
            if (!questionField) {
                throw new Error('faqQuestion field not found');
            }
            if (!answerField) {
                throw new Error('faqAnswer field not found');
            }
            if (!categoryField) {
                throw new Error('faqCategory field not found');
            }
            if (!iconField) {
                throw new Error('faqIcon field not found');
            }
            if (!sortOrderField) {
                throw new Error('sortOrder field not found');
            }
            if (!isActiveField) {
                throw new Error('isActive field not found');
            }
            if (!isFeaturedField) {
                throw new Error('isFeatured field not found');
            }
            if (!isDefaultField) {
                throw new Error('isDefault field not found');
            }
            
            questionField.value = faq.question || '';
            answerField.value = faq.answer || '';
            categoryField.value = faq.category || '';
            iconField.value = faq.icon || '';
            sortOrderField.value = faq.sort_order || 0;
            isActiveField.checked = faq.is_active == 1;
            isFeaturedField.checked = faq.is_featured == 1;
            isDefaultField.checked = faq.is_default == 1;
            
            console.log('Form populated successfully');
        } catch (error) {
            console.error('Error populating form:', error);
            showAlert('error', `Error populating form: ${error.message}`);
        }
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
</script>
@endsection