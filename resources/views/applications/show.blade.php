@extends('layouts.default-template')

@section('page')
<div class="max-w-2xl mx-auto mt-12 bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Job Application Details2</h2>
    <table class="w-full mb-6">
        <tr>
            <td class="font-semibold text-slate-700">Name:</td>
            <td>{{ $application->first_name }} {{ $application->last_name }}</td>
        </tr>
        <tr>
            <td class="font-semibold text-slate-700">Email:</td>
            <td>{{ $application->email }}</td>
        </tr>
        <tr>
            <td class="font-semibold text-slate-700">Phone:</td>
            <td>{{ $application->phone }}</td>
        </tr>
        <tr>
            <td class="font-semibold text-slate-700">Status:</td>
            <td>{{ ucfirst($application->status) }}</td>
        </tr>
        <tr>
            <td class="font-semibold text-slate-700">Submitted:</td>
            <td>{{ $application->created_at->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td class="font-semibold text-slate-700">Resume:</td>
            <td>
                @if($application->resume_path)
                <a href="{{ asset('storage/' . $application->resume_path) }}" target="_blank"
                    class="text-blue-600 underline">View Resume</a>
                @else
                <span class="text-slate-400">No resume uploaded</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="font-semibold text-slate-700">Cover Letter:</td>
            <td>{{ $application->cover_letter ?? '—' }}</td>
        </tr>
    </table>
    <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">&larr; Back</a>
</div>
@endsection