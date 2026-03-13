@extends('layouts.user_dashboard')

@section('title', 'Pre-Employment 2 Portal')

@section('content')
<div class="flex flex-col gap-8 md:gap-12">
    <div class="bg-teal-600 text-white rounded-xl shadow-lg p-6 flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl font-bold">
                {{ strtoupper(substr($user->name ?? 'P', 0, 2)) }}
            </div>
            <div>
                <h2 class="text-2xl font-semibold">Welcome, {{ $user->name ?? 'Applicant' }}!</h2>
                <p class="text-sm opacity-80">{{ $user->email ?? '' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900">Pre-Employment 2 Checklist</h3>
        <ul class="space-y-4">
            @foreach($checklistDefaults as $item)
            <li class="flex items-center">
                <span class="inline-block w-4 h-4 mr-2 bg-green-200 rounded-full"></span>
                <span>{{ $item['label'] }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection