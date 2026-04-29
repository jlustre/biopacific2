@php use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6 text-indigo-800">HR Portal Dashboard</h1>

    {{-- Facility Filter Dropdown for permitted roles --}}
    @php
        $user = Auth::user();
        $canChooseFacility = $user && ($user->hasRole('admin') || $user->hasRole('hrrd'));
        $facilities = $canChooseFacility ? \App\Models\Facility::orderBy('name')->get() : null;
        $userFacility = $user && $user->facility ? $user->facility : null;
    @endphp
    @if($canChooseFacility)
        <form method="GET" action="" class="mb-6">
            <label for="facility_id" class="block text-sm font-semibold mb-1">Select Facility:</label>
            <select name="facility_id" id="facility_id" class="form-select border-teal-300 rounded w-full max-w-xs">
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id', $userFacility ? $userFacility->id : null) == $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="ml-2 px-3 py-1 bg-teal-600 text-white rounded hover:bg-teal-700">Filter</button>
        </form>
    @elseif($userFacility)
        <div class="mb-6">
            <span class="text-sm font-semibold">Facility:</span>
            <span class="text-teal-700">{{ $userFacility->name }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- HR Portal -->
        <a href="{{ route('user.hr-portal') }}" class="block bg-indigo-100 hover:bg-indigo-200 rounded-xl shadow p-6 text-center transition">
            <i class="fas fa-users-cog text-3xl text-indigo-600 mb-2"></i>
            <div class="font-semibold text-lg mt-2">HR Portal</div>
            <div class="text-gray-600 text-sm">Access HR reports and tools</div>
        </a>
        <!-- Web Contents -->
        <a href="{{ route('admin.facilities.webcontents.testimonials') }}" class="block bg-blue-100 hover:bg-blue-200 rounded-xl shadow p-6 text-center transition">
            <i class="fas fa-globe-americas text-3xl text-blue-600 mb-2"></i>
            <div class="font-semibold text-lg mt-2">Web Contents</div>
            <div class="text-gray-600 text-sm">Manage testimonials, FAQs, news, blogs, and more</div>
        </a>
        <!-- Communications -->
        <a href="{{ route('admin.tour-requests.index') }}" class="block bg-green-100 hover:bg-green-200 rounded-xl shadow p-6 text-center transition">
            <i class="fas fa-comments text-3xl text-green-600 mb-2"></i>
            <div class="font-semibold text-lg mt-2">Communications</div>
            <div class="text-gray-600 text-sm">View and manage communications</div>
        </a>
    </div>
</div>
@endsection
