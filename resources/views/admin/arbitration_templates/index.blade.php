@extends('layouts.dashboard')

@section('content')
<div class="container py-8 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Arbitration Templates</h1>
    <a href="{{ route('admin.arbitration-templates.create') }}"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 mb-4 inline-block">Upload New Template</a>
    @if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
    @endif
    @include('admin.arbitration_templates._table_filter')
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="px-4 py-2 border">Facility</th>
                <th class="px-4 py-2 border">Type</th>
                <th class="px-4 py-2 border">File</th>
                <th class="px-4 py-2 border">View</th>
                <th class="px-4 py-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $template)
            <tr>
                <td class="px-4 py-2 border">{{ $template->facility->name ?? 'N/A' }}</td>
                <td class="px-4 py-2 border uppercase">{{ $template->template_type }}</td>
                <td class="px-4 py-2 border"><a
                        href="{{ route('admin.arbitration-templates.download', $template->id) }}"
                        class="text-blue-600 underline">Download</a></td>
                <td class="px-4 py-2 border">
                    <a href="{{ route('admin.arbitration-templates.view', $template->id) }}" target="_blank"
                        class="px-2 py-1 bg-green-600 text-white rounded">View</a>
                </td>
                <td class="px-4 py-2 border">
                    <form action="{{ route('admin.arbitration-templates.destroy', $template->id) }}" method="POST"
                        onsubmit="return confirm('Delete this template?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection