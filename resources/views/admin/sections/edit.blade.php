@extends('layouts.dashboard')

@section('title', 'Edit Section: ' . $section->name)

@section('header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Section: {{ $section->name }}</h1>
        <p class="text-gray-600 mt-1">Modify the configuration and variants for this section</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sections.show', $section->id) }}"
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Back to Details
        </a>
        @if($section->variants && count($section->variants) > 0)
            <a href="{{ route('admin.sections.preview', $section->id) }}"
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2"
               target="_blank">
                <i class="fas fa-eye"></i>
                Preview
            </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.sections.update', $section->id) }}" method="POST" class="space-y-6" x-data="sectionForm()">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Section Name *</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $section->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g. Hero Section, About Us, Services"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="component_path" class="block text-sm font-medium text-gray-700 mb-2">Component Path *</label>
                    <input type="text"
                           id="component_path"
                           name="component_path"
                           value="{{ old('component_path', $section->component_path) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g. partials.hero, partials.about"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Blade component path (dot notation)</p>
                    @error('component_path')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Describe what this section does and when to use it...">{{ old('description', $section->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="flex items-center">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $section->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Active (available for use in templates)</span>
                </label>
            </div>
        </div>

        {{-- Usage Warning --}}
        @php
            $templatesUsingSection = \App\Models\LayoutTemplate::whereJsonContains('sections', $section->slug)->count();
        @endphp
        @if($templatesUsingSection > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                    <div>
                        <h3 class="text-yellow-800 font-medium">Section In Use</h3>
                        <p class="text-yellow-700 text-sm mt-1">
                            This section is currently being used by {{ $templatesUsingSection }} layout template(s).
                            Changes to variants or configuration may affect existing layouts.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Variants Configuration --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Variants</h2>
                    <p class="text-sm text-gray-600 mt-1">Define different visual styles for this section</p>
                </div>
                <button type="button"
                        @click="addVariant()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Add Variant
                </button>
            </div>

            <div class="space-y-4" x-show="variants.length === 0">
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-palette text-3xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 mb-3">No variants defined</p>
                    <button type="button"
                            @click="addVariant()"
                            class="text-blue-600 hover:text-blue-800 font-medium">
                        Add your first variant
                    </button>
                </div>
            </div>

            <div class="space-y-4" x-show="variants.length > 0">
                <template x-for="(variant, index) in variants" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label :for="'variant_name_' + index" class="block text-sm font-medium text-gray-700 mb-2">
                                        Variant Name *
                                    </label>
                                    <input type="text"
                                           :id="'variant_name_' + index"
                                           :name="'variants[' + index + '][name]'"
                                           x-model="variant.name"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="e.g. default, minimal, featured"
                                           required>
                                </div>
                                <div>
                                    <label :for="'variant_description_' + index" class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <input type="text"
                                           :id="'variant_description_' + index"
                                           :name="'variants[' + index + '][description]'"
                                           x-model="variant.description"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="Brief description of this variant">
                                </div>
                            </div>
                            <div class="ml-3 flex items-center gap-1">
                                <a :href="'/admin/sections/{{ $section->id }}/preview/' + variant.name"
                                   class="text-blue-600 hover:text-blue-800 p-1"
                                   title="Preview variant"
                                   target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button"
                                        @click="removeVariant(index)"
                                        class="text-red-600 hover:text-red-800 p-1"
                                        title="Remove variant">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-gray-500">
                            File path: <span class="font-mono">resources/views/partials/{{ $section->slug }}/<span x-text="variant.name || 'variant-name'"></span>.blade.php</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Configuration Schema --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Configuration Schema</h2>
                    <p class="text-sm text-gray-600 mt-1">Define configurable options for this section (optional)</p>
                </div>
                <button type="button"
                        @click="addConfigOption()"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Add Option
                </button>
            </div>

            <div class="space-y-4" x-show="configOptions.length === 0">
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-cog text-3xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 mb-3">No configuration options defined</p>
                    <button type="button"
                            @click="addConfigOption()"
                            class="text-green-600 hover:text-green-800 font-medium">
                        Add configuration option
                    </button>
                </div>
            </div>

            <div class="space-y-4" x-show="configOptions.length > 0">
                <template x-for="(option, index) in configOptions" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label :for="'config_key_' + index" class="block text-sm font-medium text-gray-700 mb-2">
                                        Option Key *
                                    </label>
                                    <input type="text"
                                           :id="'config_key_' + index"
                                           :name="'config_schema[' + index + '][key]'"
                                           x-model="option.key"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="e.g. show_button, columns"
                                           required>
                                </div>
                                <div>
                                    <label :for="'config_type_' + index" class="block text-sm font-medium text-gray-700 mb-2">
                                        Type *
                                    </label>
                                    <select :id="'config_type_' + index"
                                            :name="'config_schema[' + index + '][type]'"
                                            x-model="option.type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            required>
                                        <option value="">Select type</option>
                                        <option value="boolean">Boolean</option>
                                        <option value="string">String</option>
                                        <option value="number">Number</option>
                                        <option value="select">Select</option>
                                        <option value="color">Color</option>
                                    </select>
                                </div>
                                <div>
                                    <label :for="'config_label_' + index" class="block text-sm font-medium text-gray-700 mb-2">
                                        Label *
                                    </label>
                                    <input type="text"
                                           :id="'config_label_' + index"
                                           :name="'config_schema[' + index + '][label]'"
                                           x-model="option.label"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="e.g. Show Button, Number of Columns"
                                           required>
                                </div>
                            </div>
                            <button type="button"
                                    @click="removeConfigOption(index)"
                                    class="ml-3 text-red-600 hover:text-red-800 p-1"
                                    title="Remove option">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Save Actions --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Changes will affect all templates using this section
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.sections.show', $section->id) }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function sectionForm() {
        return {
            variants: @json(old('variants', $section->variants ?? [])),
            configOptions: @json(old('config_schema', $section->config_schema ?? [])),

            addVariant() {
                this.variants.push({
                    name: '',
                    description: ''
                });
            },

            removeVariant(index) {
                this.variants.splice(index, 1);
            },

            addConfigOption() {
                this.configOptions.push({
                    key: '',
                    type: '',
                    label: ''
                });
            },

            removeConfigOption(index) {
                this.configOptions.splice(index, 1);
            }
        }
    }
</script>
@endpush
@endsection
