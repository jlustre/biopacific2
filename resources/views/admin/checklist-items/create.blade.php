@extends('layouts.dashboard', ['title' => 'Create Checklist Item'])

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Create Checklist Item</h1>
    <p class="text-gray-600 mt-2">Add a checklist item and optionally restrict it to specific positions.</p>
</div>
<div class="max-w-4xl">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <form action="{{ route('admin.checklist-items.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.checklist-items._form')
            <div class="flex gap-4 pt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Create Checklist Item
                </button>
                <a href="{{ route('admin.checklist-items.index') }}" class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-semibold">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection