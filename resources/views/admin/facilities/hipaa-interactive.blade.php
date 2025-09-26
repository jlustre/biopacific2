@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $facility->name }} — HIPAA Checklist</h1>
            <p class="text-slate-600 mt-1">Interactive checklist with database persistence</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.facilities.edit', $facility) }}"
                class="text-sm font-semibold rounded-full px-4 py-2 border text-center"
                style="border-color: {{ $facility->primary_color ?? '#0EA5E9' }}; color: {{ $facility->primary_color ?? '#0EA5E9' }};">
                Back to Facility
            </a>
        </div>
    </div>

    @livewire('hipaa-checklist-interactive', ['facility' => $facility])

    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
        <h3 class="font-semibold text-blue-900 mb-2">HIPAA Checklist Instructions:</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• Toggle the switches to mark items as complete</li>
            <li>• Changes are automatically saved to the database</li>
            <li>• The progress counter updates in real-time</li>
        </ul>
    </div>
</div>
@endsection