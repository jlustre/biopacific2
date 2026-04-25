@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Facility Uploads</h1>
    @php
        $required = "";
    @endphp
    {{-- Success & Error Messaging --}}
    @if(session('success'))
        <div class="p-3 mb-4 text-green-800 bg-green-100 border border-green-400 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 mb-4 text-red-800 bg-red-100 border border-red-400 rounded">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-3 mb-4 text-red-800 bg-red-100 border border-red-400 rounded">
            <ul class="pl-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="p-6 mb-6 bg-white rounded shadow">
        <!-- Livewire debug: If you see this, the Blade is rendering -->
        <livewire:admin.facilities.facility-upload-form />
    </div>
    <div class="p-6 bg-white rounded shadow">
        <form method="GET" class="flex flex-wrap items-end gap-4 mb-4">
            <div>
                <label class="block mb-1 text-xs font-semibold">Search by Name</label>
                <input type="text" name="search" value="{{ request('search') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input" placeholder="File name...">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Filter by Facility</label>
                <select name="facility_id" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                    <option value="">All Facilities</option>
                    @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
                    <option value="{{ $fac->id }}" @if(request('facility_id')==$fac->id) selected @endif>{{ $fac->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 ml-2 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Filter</button>
        </form>
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">File Name</th>
                    <th class="px-3 py-2 border">Type</th>
                    <th class="px-3 py-2 border">Facility</th>
                    <th class="px-3 py-2 border">Uploaded By</th>
                    <th class="px-3 py-2 border">Size</th>
                    <th class="px-3 py-2 border">Effective Dates</th>
                    <th class="px-3 py-2 border">Expires</th>
