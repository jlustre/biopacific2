@extends('layouts.member-portal')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl rounded-[2rem] border border-amber-200 bg-amber-50 p-8 text-center shadow-card">
        <p class="text-xs font-bold uppercase tracking-wide text-amber-700">Employment record</p>
        <h1 class="mt-2 text-2xl font-black text-slate-950">No employee profile linked</h1>
        <p class="mt-3 text-sm text-slate-600">
            We could not find an employee record linked to your account. Please contact HR if you believe this is an error.
        </p>
        <a href="{{ route('dashboard.index') }}" class="mt-6 inline-flex rounded-2xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-700">
            Back to dashboard
        </a>
    </div>
</section>
@endsection
