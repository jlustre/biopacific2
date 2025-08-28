@extends('layouts.dashboard')

@section('title', 'Layout Builder')

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Layout Builder</h1>
        <p class="text-gray-600 mt-1">Design and customize facility layouts by reordering sections and changing variants
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.layouts.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-th-large"></i>
            Layout Templates
        </a>
        <a href="{{ route('admin.sections.index') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-puzzle-piece"></i>
            Manage Sections
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto" x-data="layoutBuilder()">

    <!-- Debug Info -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <h3 class="text-sm font-medium text-yellow-800 mb-2">Debug Information</h3>
        <div class="text-sm text-yellow-700">
            <p><strong>Selected Facility ID:</strong> <span x-text="selectedFacilityId || 'None'"></span></p>
            <p><strong>Available Sections Count:</strong> <span x-text="availableSections.length"></span></p>
            @if (!$facilities->isEmpty())
            <p><strong>Current Facility:</strong> <span x-text="currentFacility ? currentFacility.name : 'None'"></span>
            </p>
            <p><strong>Current Template:</strong> <span x-text="currentTemplate ? currentTemplate.name : 'None'"></span>
            </p>
            @endif
            <p><strong>Sections Count:</strong> <span x-text="sections.length"></span></p>
            <p><strong>Loading:</strong> <span x-text="loading ? 'Yes' : 'No'"></span></p>
        </div>
    </div>

    <!-- Facility Selection -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Facility</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Facility ({{ $facilities->count() }} available)
                </label>
                <select x-model="selectedFacilityId"
                    @change="console.log('Facility changed to:', selectedFacilityId); loadFacilityLayout()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a facility...</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}">{{ $facility->name }} ({{ $facility->domain }})</option>
                    @endforeach
                    @if($facilities->isEmpty())
                    <option disabled>No facilities found</option>
                    @endif
                </select>
            </div>
            <div x-show="currentFacility">
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Template</label>
                <div class="flex items-center gap-2">
                    <span x-text="currentTemplate?.name" class="px-3 py-2 bg-gray-100 rounded-lg text-sm"></span>
                    <button @click="showTemplateInfo = true" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
            </div>
            <div x-show="currentFacility" class="flex items-end">
                <button @click="openPreview()"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-eye"></i>
                    Preview Layout
                </button>
            </div>
        </div>
    </div>

    <!-- Layout Builder -->
    <div x-show="currentFacility && sections.length > 0" class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Current Layout Sections -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Layout Sections</h2>
                            <p class="text-sm text-gray-600">Drag to reorder • Click to configure</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500" x-text="sections.length + ' sections'"></span>
                            <button @click="saveLayout()" :disabled="!hasChanges"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div id="sections-container" class="space-y-4">
                        <template x-for="(section, index) in sections" :key="section.slug">
                            <div class="section-item bg-gray-50 border border-gray-200 rounded-lg p-4 cursor-move hover:bg-gray-100 transition-colors"
                                :data-section="section.slug">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-semibold"
                                                x-text="index + 1"></div>
                                            <div>
                                                <h3 class="font-medium text-gray-900" x-text="section.name"></h3>
                                                <p class="text-sm text-gray-500" x-text="section.description"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <!-- Variant Selector -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-sm text-gray-600">Variant:</label>
                                            <select x-model="section.current_variant" @change="markAsChanged()"
                                                class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-blue-500">
                                                <template x-for="variant in section.available_variants" :key="variant">
                                                    <option :value="variant" x-text="variant"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Actions -->
                                        <button @click="removeSection(index)"
                                            class="text-red-600 hover:text-red-800 p-1">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <button @click="configureSection(section)"
                                            class="text-blue-600 hover:text-blue-800 p-1">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="sections.length === 0" class="text-center py-12">
                        <i class="fas fa-layer-group text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Sections</h3>
                        <p class="text-gray-500">Add sections from the panel on the right to start building your layout.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div x-show="sections.length > 0" class="mt-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="resetLayout()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-undo"></i>
                        Reset to Original
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="openSaveAsTemplate()"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Save as Template
                    </button>
                    <button @click="openDuplicateLayout()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-copy"></i>
                        Duplicate Layout
                    </button>
                </div>
            </div>
        </div>

        <!-- Available Sections Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">Available Sections</h3>
                    <p class="text-sm text-gray-600">Drag sections to add them to your layout</p>
                </div>

                <div class="p-4 space-y-3">
                    <template x-for="section in availableSections" :key="section.slug">
                        <div class="available-section bg-gray-50 border border-gray-200 rounded-lg p-3 cursor-move hover:bg-blue-50 hover:border-blue-300 transition-colors"
                            :data-section="section.slug">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 text-sm" x-text="section.name"></h4>
                                    <p class="text-xs text-gray-500" x-text="section.description"></p>
                                </div>
                                <button @click="addSection(section)" class="text-blue-600 hover:text-blue-800 p-1">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <span class="text-xs text-gray-400"
                                    x-text="section.variants.length + ' variants'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-flex items-center gap-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-600">Loading layout...</span>
        </div>
    </div>

    <!-- Save as Template Modal -->
    <div x-show="showSaveTemplate" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        x-cloak @click.self="showSaveTemplate = false">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Save as Template</h3>

                <form @submit.prevent="saveAsTemplate()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                            <input type="text" x-model="templateForm.name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea x-model="templateForm.description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" x-model="templateForm.apply_to_facility"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Apply this template to current facility</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showSaveTemplate = false"
                            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Duplicate Layout Modal -->
    <div x-show="showDuplicateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        x-cloak @click.self="showDuplicateModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Duplicate Layout</h3>

                <form @submit.prevent="duplicateLayout()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Template Name</label>
                            <input type="text" x-model="duplicateForm.name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea x-model="duplicateForm.description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showDuplicateModal = false"
                            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Duplicate Layout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    function layoutBuilder() {
    return {
        selectedFacilityId: '',
        currentFacility: null,
        currentTemplate: null,
        sections: [],
        availableSections: [],
        originalSections: [],
        loading: false,
        hasChanges: false,
        showSaveTemplate: false,
        showDuplicateModal: false,
        showTemplateInfo: false,

        templateForm: {
            name: '',
            description: '',
            apply_to_facility: false
        },

        duplicateForm: {
            name: '',
            description: ''
        },

        init() {
            console.log('=== Layout Builder Alpine.js component initialized ===');
            console.log('Initial selectedFacilityId:', this.selectedFacilityId);

            // Check for facility parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            const facilityId = urlParams.get('facility');
            console.log('URL facility parameter:', facilityId);

            if (facilityId) {
                this.selectedFacilityId = facilityId;
                this.$nextTick(() => {
                    this.loadFacilityLayout();
                });
            }

            this.initSortable();
            console.log('=== Alpine.js init complete ===');
        },

        async loadFacilityLayout() {
            console.log('=== loadFacilityLayout called ===');
            console.log('selectedFacilityId:', this.selectedFacilityId);

            if (!this.selectedFacilityId) {
                console.log('No facility selected, clearing data');
                this.currentFacility = null;
                this.sections = [];
                return;
            }

            console.log('Loading facility layout for ID:', this.selectedFacilityId);
            this.loading = true;

            try {
                const url = `/admin/layout-builder/facility/${this.selectedFacilityId}/layout`;
                console.log('Fetching URL:', url);
                console.log('Full URL will be:', window.location.origin + url);

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Received data:', data);

                this.currentFacility = data.facility;
                this.currentTemplate = data.template;
                this.sections = data.sections;
                this.availableSections = data.available_sections;
                this.originalSections = JSON.parse(JSON.stringify(data.sections));
                this.hasChanges = false;

                console.log('Layout loaded successfully:', {
                    facility: this.currentFacility,
                    template: this.currentTemplate,
                    sectionsCount: this.sections.length
                });
            } catch (error) {
                console.error('Error loading facility layout:', error);
                console.error('Error type:', error.constructor.name);
                console.error('Error message:', error.message);
                if (error instanceof TypeError) {
                    console.error('This might be a CORS or network connectivity issue');
                }
                alert('Error loading facility layout: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        initSortable() {
            this.$nextTick(() => {
                const container = document.getElementById('sections-container');
                if (container) {
                    new Sortable(container, {
                        animation: 150,
                        handle: '.drag-handle',
                        onEnd: (evt) => {
                            const movedItem = this.sections.splice(evt.oldIndex, 1)[0];
                            this.sections.splice(evt.newIndex, 0, movedItem);
                            this.markAsChanged();
                        }
                    });
                }
            });
        },

        addSection(section) {
            const newSection = {
                id: section.id,
                slug: section.slug,
                name: section.name,
                description: section.description,
                current_variant: section.variants[0] || 'default',
                available_variants: section.variants,
                config: {},
                order: this.sections.length
            };

            this.sections.push(newSection);
            this.markAsChanged();
        },

        removeSection(index) {
            if (confirm('Are you sure you want to remove this section?')) {
                this.sections.splice(index, 1);
                this.markAsChanged();
            }
        },

        markAsChanged() {
            this.hasChanges = true;
        },

        async saveLayout() {
            console.log('=== saveLayout called ===');
            console.log('hasChanges:', this.hasChanges);
            console.log('currentFacility:', this.currentFacility);
            console.log('selectedFacilityId:', this.selectedFacilityId);

            if (!this.hasChanges) {
                console.log('No changes to save');
                return;
            }

            if (!this.currentFacility || !this.currentFacility.id) {
                console.error('No current facility selected');
                alert('Please select a facility first');
                return;
            }

            try {
                const url = `/admin/layout-builder/facility/${this.currentFacility.id}/update`;
                console.log('Save URL:', url);

                const payload = {
                    sections: this.sections.map((section, index) => ({
                        slug: section.slug,
                        variant: section.current_variant,
                        config: section.config || {}
                    }))
                };
                console.log('Payload:', payload);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    this.hasChanges = false;
                    this.originalSections = JSON.parse(JSON.stringify(this.sections));
                    console.log('Layout saved successfully');
                    alert('Layout saved successfully!');
                } else {
                    console.error('Save failed:', data);
                    alert('Error saving layout: ' + (data.message || data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving layout:', error);
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                alert('Error saving layout: ' + error.message);
            }
        },

        resetLayout() {
            if (confirm('Are you sure you want to reset to the original layout?')) {
                this.sections = JSON.parse(JSON.stringify(this.originalSections));
                this.hasChanges = false;
            }
        },

        openSaveAsTemplate() {
            this.templateForm.name = `${this.currentTemplate.name} (Copy)`;
            this.templateForm.description = `Custom layout based on ${this.currentTemplate.name}`;
            this.templateForm.apply_to_facility = false;
            this.showSaveTemplate = true;
        },

        async saveAsTemplate() {
            try {
                const response = await fetch(`/admin/layout-builder/facility/${this.currentFacility.id}/save-template`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ...this.templateForm,
                        sections: this.sections.map((section, index) => ({
                            slug: section.slug,
                            variant: section.current_variant,
                            config: section.config || {}
                        }))
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showSaveTemplate = false;
                    alert('Template saved successfully!');

                    if (this.templateForm.apply_to_facility) {
                        await this.loadFacilityLayout();
                    }
                } else {
                    alert('Error saving template: ' + data.message);
                }
            } catch (error) {
                console.error('Error saving template:', error);
                alert('Error saving template');
            }
        },

        openDuplicateLayout() {
            this.duplicateForm.name = `${this.currentTemplate.name} (Duplicate)`;
            this.duplicateForm.description = `Duplicated from ${this.currentTemplate.name}`;
            this.showDuplicateModal = true;
        },

        async duplicateLayout() {
            try {
                const response = await fetch(`/admin/layout-builder/facility/${this.currentFacility.id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ...this.duplicateForm,
                        sections: this.sections.map((section, index) => ({
                            slug: section.slug,
                            variant: section.current_variant,
                            config: section.config || {}
                        }))
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showDuplicateModal = false;
                    alert('Layout duplicated successfully!');
                } else {
                    alert('Error duplicating layout: ' + data.message);
                }
            } catch (error) {
                console.error('Error duplicating layout:', error);
                alert('Error duplicating layout');
            }
        },

        openPreview() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/layout-builder/facility/${this.currentFacility.id}/preview`;
            form.target = '_blank';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);

            const sectionsInput = document.createElement('input');
            sectionsInput.type = 'hidden';
            sectionsInput.name = 'sections';
            sectionsInput.value = JSON.stringify(this.sections.map(section => ({
                slug: section.slug,
                variant: section.current_variant
            })));
            form.appendChild(sectionsInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    }
}
</script>
@endpush