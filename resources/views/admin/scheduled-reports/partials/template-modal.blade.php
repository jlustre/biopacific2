{{-- Create scheduled report template modal --}}
<div id="scheduledReportTemplateModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="templateModalTitle">
    <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col rounded-xl bg-white shadow-2xl"
        onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <div>
                <h2 id="templateModalTitle" class="text-xl font-bold text-gray-900">Create Scheduled Report Template</h2>
                <p class="mt-1 text-sm text-gray-600">Save a reusable configuration. Use it later when scheduling a report.</p>
            </div>
            <button type="button" onclick="closeScheduledReportTemplateModal()"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                aria-label="Close">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.scheduled-reports.templates.store') }}" method="POST"
            class="flex flex-1 flex-col overflow-hidden">
            @csrf
            <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                @if ($errors->any() && old('_template_modal'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold mb-1">Please fix the following:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <input type="hidden" name="_template_modal" value="1">

                <div class="rounded-lg border border-teal-100 bg-teal-50/50 p-4">
                    <h3 class="text-sm font-semibold text-teal-900 mb-3">Template identity</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Template name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required maxlength="255"
                                value="{{ old('name') }}"
                                placeholder="e.g. Monthly census — Pine Ridge"
                                class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" maxlength="1000"
                                placeholder="Optional notes for admins who use this template"
                                class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
                        </div>
                        @if(!empty($scopedFacilityId))
                        <input type="hidden" name="facility_id" value="{{ $scopedFacilityId }}">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Facility</label>
                            <input type="text" readonly value="{{ $scopedFacility->name ?? '' }}"
                                class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        </div>
                        @elseif(!empty($canManageScheduledReports) && isset($facilities) && $facilities->isNotEmpty())
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Facility (optional)</label>
                            <select name="facility_id"
                                class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                                <option value="">All facilities / global</option>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Leave blank for a template usable across facilities.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Report configuration</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Report <span class="text-red-500">*</span></label>
                            <select name="report_id" required
                                class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500">
                                <option value="">Select report</option>
                                @foreach($reports as $report)
                                <option value="{{ $report->id }}" {{ old('report_id') == $report->id ? 'selected' : '' }}>{{ $report->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Output format <span class="text-red-500">*</span></label>
                                <select name="report_format" id="template_report_format" required onchange="templateToggleOrientation()"
                                    class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm">
                                    <option value="csv" {{ old('report_format', 'csv') == 'csv' ? 'selected' : '' }}>CSV</option>
                                    <option value="pdf" {{ old('report_format') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                    <option value="html" {{ old('report_format') == 'html' ? 'selected' : '' }}>HTML</option>
                                </select>
                            </div>
                            <div id="template_pdf_orientation_group" style="display: {{ old('report_format') == 'pdf' ? 'block' : 'none' }};">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">PDF orientation</label>
                                <select name="pdf_orientation"
                                    class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm">
                                    <option value="P" {{ old('pdf_orientation', 'P') == 'P' ? 'selected' : '' }}>Portrait</option>
                                    <option value="L" {{ old('pdf_orientation') == 'L' ? 'selected' : '' }}>Landscape</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Parameters (JSON)</label>
                            <textarea name="parameters" rows="2"
                                placeholder='{"facility_id": 1}'
                                class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 font-mono text-sm">{{ old('parameters') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Optional. Passed to the report SQL when the schedule runs.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Default status <span class="text-red-500">*</span></label>
                            <select name="status" required
                                class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Schedule (CRON)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Minute</label>
                            <select id="template_cron_minute" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                <option value="*">Every</option>
                                @for($i = 0; $i < 60; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Hour</label>
                            <select id="template_cron_hour" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                <option value="*">Every</option>
                                @for($i = 0; $i < 24; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Day</label>
                            <select id="template_cron_day" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                <option value="*">Every</option>
                                @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Month</label>
                            <select id="template_cron_month" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                <option value="*">Every</option>
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Weekday</label>
                            <select id="template_cron_weekday" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                <option value="*">Every</option>
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="cron_expression" id="template_cron_expression" value="{{ old('cron_expression', '0 8 * * *') }}">
                    <p class="mt-2 text-xs text-gray-500">
                        Expression: <code id="template_cron_preview" class="bg-gray-100 px-1 rounded">{{ old('cron_expression', '0 8 * * *') }}</code>
                        · <a href="https://crontab.guru/" target="_blank" rel="noopener" class="text-teal-600 underline">CRON help</a>
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Notifications &amp; window (optional)</h3>
                    <div class="space-y-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="notifications_enabled" id="template_notifications_enabled" value="1"
                                {{ old('notifications_enabled') ? 'checked' : '' }}
                                onchange="templateToggleNotifications()">
                            <span class="ml-2 text-sm font-medium text-gray-700">Enable email notifications when report runs</span>
                        </label>
                        <div id="template_notification_recipients_group" style="display: {{ old('notifications_enabled') ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Notify roles</label>
                                <select name="notify_roles[]" multiple
                                    class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm min-h-[80px]">
                                    <option value="admin">Admin</option>
                                    <option value="facility-admin">Facility Admin</option>
                                    <option value="facility-dsd">Facility DSD</option>
                                    <option value="facility-editor">Facility Editor</option>
                                    <option value="hrrd">HRRD</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Additional email addresses</label>
                                <input type="text" name="notify_emails" value="{{ old('notify_emails') }}"
                                    placeholder="email1@example.com, email2@example.com"
                                    class="w-full rounded-md border border-teal-500 bg-white px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Suggested start</label>
                                <input type="datetime-local" name="start_at" value="{{ old('start_at') }}"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Suggested end</label>
                                <input type="datetime-local" name="end_at" value="{{ old('end_at') }}"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <p class="mt-1 text-xs text-gray-500">Leave blank for ongoing.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <button type="button" onclick="closeScheduledReportTemplateModal()"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                    <i class="fas fa-save mr-1"></i> Save Template
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openScheduledReportTemplateModal() {
    const modal = document.getElementById('scheduledReportTemplateModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    templateUpdateCron();
}

function closeScheduledReportTemplateModal() {
    const modal = document.getElementById('scheduledReportTemplateModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function templateToggleOrientation() {
    const format = document.getElementById('template_report_format')?.value;
    const group = document.getElementById('template_pdf_orientation_group');
    if (group) group.style.display = format === 'pdf' ? 'block' : 'none';
}

function templateToggleNotifications() {
    const enabled = document.getElementById('template_notifications_enabled')?.checked;
    const group = document.getElementById('template_notification_recipients_group');
    if (group) group.style.display = enabled ? 'block' : 'none';
}

function templateUpdateCron() {
    const minute = document.getElementById('template_cron_minute')?.value ?? '*';
    const hour = document.getElementById('template_cron_hour')?.value ?? '*';
    const day = document.getElementById('template_cron_day')?.value ?? '*';
    const month = document.getElementById('template_cron_month')?.value ?? '*';
    const weekday = document.getElementById('template_cron_weekday')?.value ?? '*';
    const expr = `${minute} ${hour} ${day} ${month} ${weekday}`;
    const input = document.getElementById('template_cron_expression');
    const preview = document.getElementById('template_cron_preview');
    if (input) input.value = expr;
    if (preview) preview.textContent = expr;
}

document.addEventListener('DOMContentLoaded', function () {
    ['template_cron_minute', 'template_cron_hour', 'template_cron_day', 'template_cron_month', 'template_cron_weekday'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', templateUpdateCron);
    });

    const modal = document.getElementById('scheduledReportTemplateModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeScheduledReportTemplateModal();
        });
    }

    @if(old('_template_modal') || $errors->any())
    openScheduledReportTemplateModal();
    @endif
});
</script>
@endpush
