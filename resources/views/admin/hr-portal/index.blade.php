@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4">HR Portal</h1>
    <div class="bg-white p-6 rounded shadow">
        <p>Welcome to the HR Portal. Select a facility to view its dashboard:</p>
        @if(isset($facilities) && $facilities instanceof \Illuminate\Support\Collection)
        @if($facilities->isEmpty())
        <div class="text-red-600 mt-4">You do not have access to any facility HR Portal.</div>
        @else
        <ul class="mt-4 list-disc list-inside">
            @foreach($facilities as $facility)
            <li>
                <a href="{{ route('admin.facility.dashboard', ['facility' => $facility->slug]) }}"
                    class="text-blue-600 hover:underline">
                    {{ $facility->name }}
                </a>
            </li>
            @endforeach
        </ul>
        @endif
        @else
        <div class="text-red-600 mt-4">Facilities data is not available.</div>
        @endif
    </div>
</div>
@endsection