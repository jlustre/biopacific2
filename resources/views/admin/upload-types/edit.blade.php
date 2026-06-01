@extends('layouts.dashboard', ['title' => 'Edit Document Type'])

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Edit Document Type</h1>
        <p class="text-sm text-slate-500">Update document type settings and classification tags.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.upload-types.update', $uploadType) }}">
        @include('admin.upload-types._form', ['uploadType' => $uploadType, 'submitLabel' => 'Update', 'method' => 'PUT'])
    </form>
</div>
@endsection
