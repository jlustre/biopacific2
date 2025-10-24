@props(['success' => null, 'error' => null, 'errors' => []])

@if($success)
<div class="alert mb-4 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M5 10l3 3 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            </svg>
            <div>{{ $success }}</div>
        </div>
        <button type="button" aria-label="Dismiss" onclick="this.closest('.alert').remove()"
            class="text-green-600 hover:text-green-800">×</button>
    </div>
</div>
@endif

@if($error)
<div class="alert mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M10 5v6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                <circle cx="10" cy="14" r="1" fill="currentColor" />
            </svg>
            <div>{{ $error }}</div>
        </div>
        <button type="button" aria-label="Dismiss" onclick="this.closest('.alert').remove()"
            class="text-rose-600 hover:text-rose-800">×</button>
    </div>
</div>
@endif

@if($errors)
<div class="alert mb-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
    <div class="flex items-start justify-between gap-4">
        <div>
            <strong class="block font-semibold">There were some problems with your submission:</strong>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach($errors as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" aria-label="Dismiss" onclick="this.closest('.alert').remove()"
            class="text-rose-600 hover:text-rose-800">×</button>
    </div>
</div>
@endif