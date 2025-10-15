@extends('layouts.user_dashboard', ['title' => 'Dashboard'])


@section('content')
<div class="flex flex-col gap-8 md:gap-12">
    <!-- User Welcome Card -->
    <div
        class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white rounded-xl shadow-lg p-6 flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl font-bold">
                {{ Auth::user()->initials() }}
            </div>
            <div>
                <h2 class="text-2xl font-semibold">Welcome, {{ Auth::user()->name }}!</h2>
                <p class="text-sm opacity-80">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('settings.profile') }}"
                class="inline-block px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold shadow hover:bg-indigo-50 transition">Edit
                Profile</a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <div class="text-indigo-600 text-3xl mb-2"><i class="fas fa-hospital"></i></div>
            <div class="text-2xl font-bold">{{ \App\Models\Facility::count() }}</div>
            <div class="text-sm text-gray-500">Facilities</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <div class="text-pink-500 text-3xl mb-2"><i class="fas fa-users"></i></div>
            <div class="text-2xl font-bold">{{ \App\Models\User::count() }}</div>
            <div class="text-sm text-gray-500">Users</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <div class="text-green-500 text-3xl mb-2"><i class="fas fa-question-circle"></i></div>
            <div class="text-2xl font-bold">{{ \App\Models\Faq::count() }}</div>
            <div class="text-sm text-gray-500">FAQs</div>
        </div>
    </div>

    <!-- Recent Activity (placeholder) -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
        <ul class="divide-y divide-gray-200">
            <li class="py-2 flex items-center gap-2 text-gray-700"><i class="fas fa-check-circle text-green-500"></i>
                Your account was last updated 2 days ago.</li>
            <li class="py-2 flex items-center gap-2 text-gray-700"><i class="fas fa-hospital text-indigo-500"></i> 3 new
                facilities added this week.</li>
            <li class="py-2 flex items-center gap-2 text-gray-700"><i class="fas fa-question-circle text-pink-500"></i>
                5 new FAQs published.</li>
        </ul>
    </div>

    <!-- Facility Highlights -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Featured Facilities</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(\App\Models\Facility::orderBy('is_active', 'desc')->take(3)->get() as $facility)
            <div class="border rounded-lg p-4 flex flex-col gap-2 hover:shadow-lg transition">
                <div class="font-bold text-indigo-600 text-xl">{{ $facility->name }}</div>
                <div class="text-sm text-gray-500">{{ $facility->city }}, {{ $facility->state }}</div>
                <div class="text-xs text-gray-400">Beds: {{ $facility->beds ?? 'N/A' }}</div>
                <a href="{{ route('facility.public', $facility->slug) }}"
                    class="mt-2 inline-block text-indigo-500 hover:underline">View Details</a>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Helpful Links -->
    <div class="bg-white rounded-xl shadow p-6 flex flex-wrap gap-4 justify-between items-center">
        <a href="{{ route('faqs.index') }}" class="text-indigo-600 hover:underline font-semibold"><i
                class="fas fa-question-circle"></i> FAQs</a>
        <a href="{{ route('tours.form') }}" class="text-pink-500 hover:underline font-semibold"><i
                class="fas fa-calendar-alt"></i> Book a Tour</a>
        <a href="{{ route('settings.profile') }}" class="text-green-500 hover:underline font-semibold"><i
                class="fas fa-user-cog"></i> Profile Settings</a>
    </div>
</div>
@endsection