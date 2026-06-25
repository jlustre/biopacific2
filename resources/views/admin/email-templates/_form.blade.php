@php
$placeholders = [
    '{first_name}',
    '{last_name}',
    '{facility_name}',
    '{job_title}',
    '{application_id}',
    '{employee_num}',
    '{applicant_code}',
    '{pre_employment_link}',
    '{registration_code}',
    '{registration_link}',
    '{registration_expiration}',
    '{verification_link}',
    '{dashboard_link}',
];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
            <input type="text" name="name" value="{{ old('name', $emailTemplate->name ?? '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select a Category --</option>
                <option value="applicant" {{ old('category', $emailTemplate->category ?? '') === 'applicant' ?
                    'selected' : '' }}>
                    Applicant & Job Applications
                </option>
                <option value="resident" {{ old('category', $emailTemplate->category ?? '') === 'resident' ? 'selected'
                    : '' }}>
                    Resident/Patient Communication
                </option>
                <option value="family" {{ old('category', $emailTemplate->category ?? '') === 'family' ? 'selected' : ''
                    }}>
                    Family & Next of Kin
                </option>
                <option value="staff" {{ old('category', $emailTemplate->category ?? '') === 'staff' ? 'selected' : ''
                    }}>
                    Staff Communication
                </option>
                <option value="onboarding" {{ old('category', $emailTemplate->category ?? '') === 'onboarding' ?
                    'selected' : '' }}>
                    Onboarding & Training
                </option>
                <option value="regulatory" {{ old('category', $emailTemplate->category ?? '') === 'regulatory' ?
                    'selected' : '' }}>
                    Regulatory & Compliance
                </option>
                <option value="administrative" {{ old('category', $emailTemplate->category ?? '') === 'administrative' ?
                    'selected' : '' }}>
                    Administrative
                </option>
                <option value="appointments" {{ old('category', $emailTemplate->category ?? '') === 'appointments' ?
                    'selected' : '' }}>
                    Appointments & Reminders
                </option>
                <option value="discharge" {{ old('category', $emailTemplate->category ?? '') === 'discharge' ?
                    'selected' : '' }}>
                    Discharge & Transfer
                </option>
                <option value="feedback" {{ old('category', $emailTemplate->category ?? '') === 'feedback' ? 'selected'
                    : '' }}>
                    Feedback & Complaints
                </option>
                <option value="internal" {{ old('category', $emailTemplate->category ?? '') === 'internal' ? 'selected'
                    : '' }}>
                    Internal Announcements
                </option>
            </select>
            @error('category')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <input type="text" name="subject" value="{{ old('subject', $emailTemplate->subject ?? '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('subject')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
            <textarea id="email-template-body" name="body" rows="12"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Write the email body here...">{{ old('body', $emailTemplate->body ?? '') }}</textarea>
            @error('body')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{ old('is_active',
                $emailTemplate->is_active ?? true) ? 'checked' : '' }}>
            <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4 h-fit">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Available Placeholders</h3>
        <ul class="space-y-2 text-sm text-gray-700">
            @foreach($placeholders as $placeholder)
            <li class="flex items-center justify-between">
                <span>{{ $placeholder }}</span>
                <button type="button" class="text-blue-600 hover:text-blue-800"
                    onclick="window.insertEmailTemplatePlaceholder && window.insertEmailTemplatePlaceholder('{{ $placeholder }}')">
                    Insert
                </button>
            </li>
            @endforeach
        </ul>
        <p class="text-xs text-gray-500 mt-4">Click Insert to add placeholders at the cursor in the body editor.</p>
    </div>
</div>

@once
@push('scripts')
<script src="https://cdn.tiny.cloud/1/hggcx7g2kfrgugocare6vapc39m9hxb4unvnk9nui4od2ftg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const EDITOR_ID = 'email-template-body';

    window.insertEmailTemplatePlaceholder = function (placeholder) {
        const editor = typeof tinymce !== 'undefined' ? tinymce.get(EDITOR_ID) : null;
        if (editor) {
            editor.insertContent(placeholder);
            editor.focus();
            return;
        }
        const textarea = document.getElementById(EDITOR_ID);
        if (!textarea) {
            return;
        }
        const start = textarea.selectionStart ?? textarea.value.length;
        const end = textarea.selectionEnd ?? textarea.value.length;
        textarea.value = textarea.value.slice(0, start) + placeholder + textarea.value.slice(end);
        textarea.focus();
    };

    function initEmailTemplateTinyMCE() {
        if (typeof tinymce === 'undefined' || !document.getElementById(EDITOR_ID)) {
            return;
        }

        if (tinymce.get(EDITOR_ID)) {
            return;
        }

        tinymce.init({
            selector: '#' + EDITOR_ID,
            menubar: false,
            plugins: 'lists link autolink table code',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | removeformat code',
            min_height: 280,
            max_height: 520,
            branding: false,
            promotion: false,
            resize: true,
            convert_urls: false,
            setup: function (editor) {
                editor.on('change keyup', function () {
                    editor.save();
                });
            },
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initEmailTemplateTinyMCE();

        document.querySelectorAll('form[data-email-template-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
            });
        });
    });
})();
</script>
@endpush
@endonce