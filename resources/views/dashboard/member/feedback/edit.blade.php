@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <a href="{{ route('member.feedback.show', $submission) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Back to submission</a>
        <h1 class="mt-3 text-2xl font-black text-slate-900">Edit submission</h1>
        <p class="mt-2 text-sm text-slate-600">Update details while this report is still open.</p>
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

    <form method="POST" action="{{ route('member.feedback.update', $submission) }}" enctype="multipart/form-data"
          class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">What are you submitting?</label>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($categoryOptions as $value => $label)
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 {{ old('category', $submission->category) === $value ? 'border-teal-500 bg-teal-50 ring-1 ring-teal-200' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="category" value="{{ $value }}" class="mt-1"
                           @checked(old('category', $submission->category) === $value) onchange="toggleUrgentField()">
                    <span class="text-sm font-semibold text-slate-900">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        @if($facilities->count() > 1)
        <div>
            <label for="facility_id" class="mb-1 block text-sm font-semibold text-slate-700">Related facility</label>
            <select name="facility_id" id="facility_id" required
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" @selected((string) old('facility_id', $submission->facility_id) === (string) $facility->id)>
                    {{ $facility->name }}
                </option>
                @endforeach
            </select>
        </div>
        @elseif($facilities->count() === 1)
        <input type="hidden" name="facility_id" value="{{ $facilities->first()->id }}">
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Your name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $submission->name) }}" required
                       class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Your email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $submission->email) }}" required
                       class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label for="subject" class="mb-1 block text-sm font-semibold text-slate-700">Subject</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject', $submission->subject) }}" required
                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
        </div>

        <div>
            <label for="message" class="mb-1 block text-sm font-semibold text-slate-700">Details</label>
            <textarea name="message" id="message" rows="6" required
                      class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">{{ old('message', $submission->message) }}</textarea>
        </div>

        @if(!empty($submission->screenshots))
        <div>
            <p class="mb-2 text-sm font-semibold text-slate-700">Current screenshots</p>
            <div class="flex flex-wrap gap-3">
                @foreach($submission->screenshots as $screenshot)
                <img src="{{ asset('storage/' . $screenshot) }}" alt="Screenshot" class="h-20 rounded-lg border border-slate-200 object-cover">
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <label for="screenshots" class="mb-1 block text-sm font-semibold text-slate-700">Add screenshots (optional)</label>
            <p class="mb-2 text-xs text-slate-500">New uploads are added to existing screenshots (max 5 total).</p>
            <input type="file" name="screenshots[]" id="screenshots" accept="image/*" multiple
                   class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-700 hover:file:bg-teal-100">
        </div>

        <div id="urgent-field" class="flex items-center gap-2">
            <input type="hidden" name="urgent" value="0">
            <input type="checkbox" name="urgent" id="urgent" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                   @checked(old('urgent', $submission->urgent))>
            <label for="urgent" class="text-sm text-rose-700">Mark as urgent (issues blocking work only)</label>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                Save changes
            </button>
            <a href="{{ route('member.feedback.show', $submission) }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</section>

<script>
function toggleUrgentField() {
    const selected = document.querySelector('input[name="category"]:checked');
    const urgentField = document.getElementById('urgent-field');
    if (!selected || !urgentField) return;
    urgentField.style.display = selected.value === 'enhancement' ? 'none' : 'flex';
    if (selected.value === 'enhancement') {
        const urgent = document.getElementById('urgent');
        if (urgent) urgent.checked = false;
    }
}
document.addEventListener('DOMContentLoaded', toggleUrgentField);
</script>
@endsection
