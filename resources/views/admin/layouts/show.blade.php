@extends('layouts.dashboard')

@section('title', $template->name . ' - Layout Details')

@section('content')
<div class="bg-white min-h-screen">
    <!-- Header -->
    <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.layouts.index') }}"
                   class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $template->name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $template->slug }}</p>
                </div>
                <div class="flex items-center">
                    @if($template->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-pause-circle mr-2"></i>
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.layouts.preview', $template->id) }}"
                   target="_blank"
                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-eye mr-2"></i>
                    Preview
                </a>
                <a href="{{ route('admin.layouts.edit', $template->id) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Template
                </a>
            </div>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Template Information -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Template Information</h2>

                    <!-- Preview Image -->
                    @if($template->preview_image)
                        <div class="mb-4">
                            <img src="{{ $template->preview_image }}"
                                 alt="{{ $template->name }}"
                                 class="w-full h-32 object-cover rounded-lg">
                        </div>
                    @endif

                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="text-sm text-gray-900">{{ $template->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Slug</dt>
                            <dd class="text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded">{{ $template->slug }}</dd>
                        </div>
                        @if($template->description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="text-sm text-gray-900">{{ $template->description }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="text-sm">
                                @if($template->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-gray-600">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Facilities Using</dt>
                            <dd class="text-sm text-gray-900">{{ $template->facilities->count() }} facilities</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $template->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Modified</dt>
                            <dd class="text-sm text-gray-900">{{ $template->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </dl>

                    <!-- Actions -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex flex-col gap-2">
                            <button onclick="duplicateTemplate({{ $template->id }})"
                                    class="w-full text-center py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-copy mr-2"></i>
                                Duplicate Template
                            </button>
                            @if($template->facilities->count() == 0)
                                <button onclick="deleteTemplate({{ $template->id }})"
                                        class="w-full text-center py-2 px-4 border border-red-300 rounded-md text-sm font-medium text-red-700 hover:bg-red-50">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Template
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Facilities Using This Template -->
                @if($template->facilities->count() > 0)
                    <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Facilities Using This Template</h3>
                        <div class="space-y-2">
                            @foreach($template->facilities as $facility)
                                <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $facility->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $facility->domain }}</p>
                                    </div>
                                    <a href="{{ route('admin.facilities.edit', $facility->id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Template Structure -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Template Structure</h2>

                    @if(empty($template->sections))
                        <div class="text-center py-8">
                            <i class="fas fa-th-large text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No sections configured for this template.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($template->sections as $index => $sectionSlug)
                                @php
                                    $section = $sections->where('slug', $sectionSlug)->first();
                                    $config = $template->default_config[$sectionSlug] ?? [];
                                    $variant = $config['variant'] ?? 'default';
                                @endphp

                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium">
                                                {{ $index + 1 }}
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    {{ $section ? $section->name : ucfirst(str_replace(['_', '-'], ' ', $sectionSlug)) }}
                                                </h3>
                                                <p class="text-xs text-gray-500">{{ $sectionSlug }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ ucfirst($variant) }} Variant
                                            </span>
                                            @if($section && $section->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($section && $section->description)
                                        <p class="text-sm text-gray-600 mt-2 ml-11">{{ $section->description }}</p>
                                    @endif

                                    <!-- Configuration Preview -->
                                    @if(!empty($config))
                                        <div class="mt-3 ml-11">
                                            <details class="group">
                                                <summary class="cursor-pointer text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                    <i class="fas fa-cog mr-1"></i>
                                                    View Configuration
                                                </summary>
                                                <div class="mt-2 p-3 bg-gray-50 rounded text-xs">
                                                    <pre class="text-gray-700 whitespace-pre-wrap">{{ json_encode($config, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </details>
                                        </div>
                                    @endif

                                    <!-- Available Variants -->
                                    @if($section && !empty($section->variants))
                                        <div class="mt-3 ml-11">
                                            <p class="text-xs text-gray-500 mb-1">Available variants:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($section->variants as $availableVariant)
                                                    @php
                                                        $variantName = is_array($availableVariant) ? ($availableVariant['name'] ?? 'default') : (is_object($availableVariant) ? ($availableVariant->name ?? 'default') : $availableVariant);
                                                        $isSelected = $variantName === $variant;
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                        {{ $isSelected ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                                                        {{ ucfirst($variantName) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Default Configuration -->
                @if(!empty($template->default_config))
                    <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Default Configuration</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-700 whitespace-pre-wrap overflow-auto">{{ json_encode($template->default_config, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function duplicateTemplate(templateId) {
    if (confirm('Duplicate this layout template?')) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/layouts/${templateId}/duplicate`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this layout template? This action cannot be undone.')) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/layouts/${templateId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
