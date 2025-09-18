@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">{{ $facility->name }} — HIPAA Website Readiness</h1>
        <a href="{{ route('admin.facilities.edit', $facility) }}"
            class="text-sm font-semibold rounded-full px-4 py-2 border"
            style="border-color: {{ $facility->primary_color ?? '#0EA5E9' }}; color: {{ $facility->primary_color ?? '#0EA5E9' }};">
            Back to Facility
        </a>
    </div>

    @livewire('admin.hipaa-checklist', ['facility' => $facility])
</div>
@endsection