@extends('layouts.dashboard', ['title' => 'Email Templates'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Email Templates</h1>
            <p class="text-gray-600">Manage reusable email templates for system communications.</p>
        </div>
        <a href="{{ route('admin.email-templates.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i>New Template
        </a>
    </div>

    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(request('reply_to'))
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-envelope text-blue-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-blue-900">
                    <strong>Reply Mode:</strong> Select a template to reply to <strong>{{ request('applicant_name')
                        }}</strong> at <strong>{{ request('reply_to') }}</strong>
                </p>
                <p class="text-xs text-blue-700 mt-1">
                    Click on a template to load it, customize the content, and send your reply.
                </p>
                <a href="{{ route('admin.job-applications.show', request('job_application_id')) }}"
                    class="text-blue-700 hover:text-blue-900 text-xs font-semibold mt-2 inline-block">
                    <i class="fas fa-times mr-1"></i>Cancel Reply
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <form method="GET" action="{{ route('admin.email-templates.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search by name, category, or subject"
                class="w-full sm:flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.email-templates.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Updated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($emailTemplates as $template)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $template->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $template->category }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $template->subject }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $template->updated_at->format('M j, Y') }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex items-center gap-3">
                                @if(request('reply_to'))
                                @php
                                $jobAppStatus =
                                optional(App\Models\JobApplication::find(request('job_application_id')))->status;
                                @endphp
                                @if($jobAppStatus === 'pre-employment')
                                <a href="{{ route('admin.email-templates.show', ['email_template' => $template, 'reply_to' => request('reply_to'), 'job_application_id' => request('job_application_id'), 'applicant_name' => request('applicant_name')]) }}"
                                    class="text-green-600 hover:text-green-900 font-semibold">Use for Reply</a>
                                @else
                                <span class="text-gray-400 font-semibold cursor-not-allowed"
                                    title="Status must be pre-employment">Use for Reply</span>
                                @endif
                                @else
                                <a href="{{ route('admin.email-templates.show', $template) }}"
                                    class="text-indigo-600 hover:text-indigo-900">View</a>
                                @endif
                                @if(!request('reply_to'))
                                <a href="{{ route('admin.email-templates.edit', $template) }}"
                                    class="text-blue-600 hover:text-blue-900">Edit</a>
                                @endif
                                @if(!request('reply_to'))
                                <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this template?')"
                                        class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No email templates found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($emailTemplates->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $emailTemplates->links() }}
        </div>
        @endif
    </div>
</div>
@endsection