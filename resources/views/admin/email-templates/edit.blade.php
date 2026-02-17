@extends('layouts.dashboard', ['title' => 'Edit Email Template'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Email Template</h1>
            <p class="text-gray-600">Update the template content and status.</p>
        </div>
        <a href="{{ route('admin.email-templates.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form method="POST" action="{{ route('admin.email-templates.update', $emailTemplate) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.email-templates._form', ['emailTemplate' => $emailTemplate])

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection