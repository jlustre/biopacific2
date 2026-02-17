@extends('layouts.dashboard', ['title' => 'Create Email Template'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Email Template</h1>
            <p class="text-gray-600">Define the subject and body for reusable emails.</p>
        </div>
        <a href="{{ route('admin.email-templates.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form method="POST" action="{{ route('admin.email-templates.store') }}" class="space-y-6">
            @csrf
            @include('admin.email-templates._form', ['emailTemplate' => null])

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection