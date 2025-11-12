@extends('layouts.dashboard')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <form id="facilitySwitchForm" class="mb-6 flex items-center justify-center">
        <label for="facilitySwitch" class="text-sm font-semibold mr-2">Switch Facility:</label>
        <select id="facilitySwitch" class="border border-teal-600 px-2 py-1 rounded" onchange="switchFacility()">
            @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
            <option value="{{ $fac->slug }}" {{ $fac->id == $facility->id ? 'selected' : '' }}>{{ $fac->name }}</option>
            @endforeach
        </select>
    </form>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $facility->name }} — HIPAA Checklist</h1>
            <p class="text-slate-600 mt-1">Interactive checklist with database persistence</p>
        </div>
        <div class="flex gap-2 items-center">
            <a href="{{ route('admin.facilities.edit', $facility) }}"
                class="text-sm font-semibold rounded-full px-4 py-2 border text-center"
                style="border-color: {{ $facility->primary_color ?? '#0EA5E9' }}; color: {{ $facility->primary_color ?? '#0EA5E9' }};">
                Back to Facility
            </a>
        </div>
    </div>
    <script>
        function switchFacility() {
            var slug = document.getElementById('facilitySwitch').value;
            if (slug) {
                window.location.href = '/admin/facilities/' + slug + '/hipaa-interactive';
            }
        }
    </script>

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