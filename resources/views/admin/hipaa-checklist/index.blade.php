@extends('layouts.dashboard')

@section('content')
<h1 class="text-teal-600 text-2xl font-bold text-center mb-4">HIPAA Compliance Checklist</h1>
<p class="mb-4 text-gray-700 text-center">Select a facility to view or manage its HIPAA compliance checklist.</p>
<form id="facilitySelectForm" class="max-w-md mx-auto mb-8">
    <label for="facilitySelect" class="block font-bold mb-2">Facility</label>
    <select id="facilitySelect" class="w-full border px-2 py-2 rounded" onchange="goToFacilityChecklist()">
        <option value="">-- Select Facility --</option>
        @foreach(App\Models\Facility::orderBy('name')->get() as $facility)
        <option value="{{ $facility->slug }}">{{ $facility->name }}</option>
        @endforeach
    </select>
</form>
<script>
    function goToFacilityChecklist() {
		var slug = document.getElementById('facilitySelect').value;
		if (slug) {
			window.location.href = '/admin/facilities/' + slug + '/hipaa-interactive';
		}
	}
</script>
@endsection