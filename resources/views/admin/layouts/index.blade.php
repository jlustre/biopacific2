@extends('layouts.dashboard')

@section('title', 'Layout Templates')

@section('content')
<div class="bg-white min-h-screen">
    <!-- Header -->
    <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Layout Templates</h1>
                <p class="mt-1 text-sm text-gray-500">Manage and customize layout templates for your facilities</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.layouts.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create New Layout
                </a>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="p-6">
        @if($templates->isEmpty())
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <i class="fas fa-th-large text-4xl"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No layout templates</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new layout template.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.layouts.create') }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                        <i class="fas fa-plus mr-2"></i>
                        Create New Layout
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <!-- Preview Image -->
                        <div class="aspect-video bg-gray-100 rounded-t-lg overflow-hidden">
                            @if($template->preview_image)
                                <img src="{{ $template->preview_image }}"
                                     alt="{{ $template->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                                    <div class="text-center">
                                        <i class="fas fa-th-large text-4xl text-blue-400 mb-2"></i>
                                        <p class="text-sm text-blue-600 font-medium">{{ $template->name }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Template Info -->
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $template->name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ $template->slug }}</p>
                                    @if($template->description)
                                        <p class="text-sm text-gray-600 mt-2">{{ Str::limit($template->description, 80) }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center">
                                    @if($template->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Sections Preview -->
                            <div class="mt-3">
                                <p class="text-xs text-gray-500 mb-2">Sections ({{ count($template->sections ?? []) }}):</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($template->sections ?? [] as $section)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ ucfirst(str_replace(['_', '-'], ' ', $section)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Usage Stats -->
                            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                                <span>
                                    <i class="fas fa-building mr-1"></i>
                                    {{ $template->facilities_count }} {{ Str::plural('facility', $template->facilities_count) }}
                                </span>
                                <span>{{ $template->updated_at->format('M j, Y') }}</span>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.layouts.preview', $template->id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                       target="_blank">
                                        <i class="fas fa-eye mr-1"></i>
                                        Preview
                                    </a>
                                    <a href="{{ route('admin.layouts.show', $template->id) }}"
                                       class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Details
                                    </a>
                                </div>

                                <div class="flex gap-1">
                                    <a href="{{ route('admin.layouts.edit', $template->id) }}"
                                       class="text-gray-400 hover:text-gray-600"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="duplicateTemplate({{ $template->id }})"
                                            class="text-gray-400 hover:text-gray-600"
                                            title="Duplicate">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    @if($template->facilities_count == 0)
                                        <button onclick="deleteTemplate({{ $template->id }})"
                                                class="text-red-400 hover:text-red-600"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Delete Layout Template</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete this layout template? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDelete"
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-700">
                    Delete
                </button>
                <button onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let templateToDelete = null;

function deleteTemplate(templateId) {
    templateToDelete = templateId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    templateToDelete = null;
}

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

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (templateToDelete) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/layouts/${templateToDelete}`;

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
});
</script>
@endpush
