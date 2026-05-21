@extends('layouts.dashboard', ['title' => 'Edit Import Preset'])

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.import-mapping-presets.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Back to presets</a>
    <h1 class="mt-3 text-3xl font-bold text-slate-900">Edit: {{ $preset->name }}</h1>
    <p class="mt-2 text-slate-600">Update preset details and column mappings.</p>
</div>

@if(session('success'))
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <ul class="list-inside list-disc">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.import-mapping-presets.update', $preset) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.import-mapping-presets.partials.form', ['preset' => $preset])
    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">Save changes</button>
        <a href="{{ route('admin.import-mapping-presets.show', $preset) }}" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">View</a>
        <a href="{{ route('admin.import-mapping-presets.index') }}" class="rounded-xl border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
    </div>
</form>
@endsection
