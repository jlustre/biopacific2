@extends('layouts.dashboard')

@section('header')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Scheduled Report</h1>
        <p class="text-gray-600 mt-2">Update the schedule, parameters, or status for this report.</p>
    </div>
    <a href="{{ route('admin.scheduled-reports.index') }}"
        class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Back to List
    </a>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.scheduled-reports.update', $scheduledReport) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Details</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Report Name</label>
                    <input type="text" name="name" class="form-input w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" required value="{{ old('name', $scheduledReport->name) }}">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Report</label>
                    <select name="report_id" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" required>
                        <option value="">Select Report</option>
                        @foreach($reports as $report)
                        <option value="{{ $report->id }}" {{ (old('report_id', $scheduledReport->report_id) == $report->id) ? 'selected' : '' }}>{{ $report->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Report Format</label>
                    <select name="report_format" id="report_format" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" required onchange="toggleOrientation()">
                        <option value="csv" {{ old('report_format', $scheduledReport->report_format ?? 'csv') == 'csv' ? 'selected' : '' }}>CSV</option>
                        <option value="pdf" {{ old('report_format', $scheduledReport->report_format) == 'pdf' ? 'selected' : '' }}>PDF</option>
                        <option value="html" {{ old('report_format', $scheduledReport->report_format) == 'html' ? 'selected' : '' }}>HTML</option>
                    </select>
                    <span class="text-xs text-gray-500">Choose the file format for the generated report.</span>
                </div>
                <div id="pdf_orientation_group" style="display: {{ old('report_format', $scheduledReport->report_format) == 'pdf' ? 'block' : 'none' }};">
                    <label class="block text-gray-700 font-semibold mb-1">PDF Orientation</label>
                    <select name="pdf_orientation" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                        <option value="P" {{ old('pdf_orientation', $scheduledReport->pdf_orientation) == 'P' ? 'selected' : '' }}>Portrait</option>
                        <option value="L" {{ old('pdf_orientation', $scheduledReport->pdf_orientation) == 'L' ? 'selected' : '' }}>Landscape</option>
                    </select>
                    <span class="text-xs text-gray-500">Choose page orientation for PDF output.</span>
                </div>
                <script>
                function toggleOrientation() {
                    var format = document.getElementById('report_format').value;
                    document.getElementById('pdf_orientation_group').style.display = (format === 'pdf') ? 'block' : 'none';
                }
                document.addEventListener('DOMContentLoaded', function() {
                    toggleOrientation();
                });
                </script>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        Parameters (JSON)
                        <span class="ml-1 cursor-pointer relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <i class="fas fa-info-circle text-teal-500"></i>
                            <span x-show="open" class="absolute left-6 top-0 z-10 w-64 p-2 bg-white border border-teal-400 text-xs text-gray-700 rounded shadow-lg" style="display:none;">
                                Custom values to pass to the report when it runs.<br>
                                Example: <code class='bg-gray-100 px-1 rounded'>{"facility_id":1}</code><br>
                                Leave blank if not needed.
                            </span>
                        </span>
                    </label>
                    <textarea name="parameters" class="form-input w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" rows="2">{{ old('parameters', json_encode($scheduledReport->parameters)) }}</textarea>
                    <span class="text-xs text-gray-500">Optional. Example: {"facility_id":1}</span>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="notifications_enabled" id="notifications_enabled" value="1" {{ old('notifications_enabled', $scheduledReport->notifications_enabled) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700 font-semibold">Enable Notifications</span>
                    </label>
                </div>
                <div id="notification_recipients_group" style="display: {{ old('notifications_enabled', $scheduledReport->notifications_enabled) ? 'block' : 'none' }};">
                    <label class="block text-gray-700 font-semibold mb-1">Notification Recipients</label>
                    <div class="mb-2">
                        <label class="block text-xs font-semibold mb-1">Notify Roles</label>
                        <select name="notify_roles[]" multiple class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white notify-roles-select">
                            <option value="">Select roles...</option>
                            <option value="admin" {{ (collect(old('notify_roles', $scheduledReport->notify_roles))->contains('admin')) ? 'selected' : '' }}>Admin</option>
                            <option value="facility-admin" {{ (collect(old('notify_roles', $scheduledReport->notify_roles))->contains('facility-admin')) ? 'selected' : '' }}>Facility Admin</option>
                            <option value="facility-dsd" {{ (collect(old('notify_roles', $scheduledReport->notify_roles))->contains('facility-dsd')) ? 'selected' : '' }}>Facility DSD</option>
                            <option value="facility-editor" {{ (collect(old('notify_roles', $scheduledReport->notify_roles))->contains('facility-editor')) ? 'selected' : '' }}>Facility Editor</option>
                            <option value="hrrd" {{ (collect(old('notify_roles', $scheduledReport->notify_roles))->contains('hrrd')) ? 'selected' : '' }}>HRRD</option>
                            <option value="regular-user" {{ (collect(old('notify_roles', $scheduledReport->notify_roles))->contains('regular-user')) ? 'selected' : '' }}>Regular User</option>
                        </select>
                        <span class="text-xs text-gray-500">Users with these roles will be notified by email.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1">Notify Email Addresses</label>
                        <input type="text" name="notify_emails" class="form-input w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" placeholder="Enter email addresses, separated by commas" value="{{ old('notify_emails', $scheduledReport->notify_emails) }}">
                        <span class="text-xs text-gray-500">You can enter multiple emails separated by commas.</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Start Date/Time</label>
                        <input type="datetime-local" name="start_at" class="form-input w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" value="{{ old('start_at', $scheduledReport->start_at ? $scheduledReport->start_at->format('Y-m-d\TH:i') : null) }}">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">End Date/Time</label>
                        <input type="datetime-local" name="end_at" class="form-input w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white" value="{{ old('end_at', $scheduledReport->end_at ? $scheduledReport->end_at->format('Y-m-d\TH:i') : null) }}">
                        <span class="text-xs text-gray-500">Leave blank for ongoing schedule.</span>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Schedule</label>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Month</label>
                            <select id="cron_month" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                                <option value="*">Every</option>
                                @for($i=1;$i<=12;$i++)
                                    <option value="{{$i}}" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[3] ?? '*') == $i ? 'selected' : '' }}>{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Weekday</label>
                            <select id="cron_weekday" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                                <option value="*">Every</option>
                                <option value="0" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '0' ? 'selected' : '' }}>Sunday</option>
                                <option value="1" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '1' ? 'selected' : '' }}>Monday</option>
                                <option value="2" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '2' ? 'selected' : '' }}>Tuesday</option>
                                <option value="3" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '3' ? 'selected' : '' }}>Wednesday</option>
                                <option value="4" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '4' ? 'selected' : '' }}>Thursday</option>
                                <option value="5" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '5' ? 'selected' : '' }}>Friday</option>
                                <option value="6" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[4] ?? '*') == '6' ? 'selected' : '' }}>Saturday</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Day</label>
                            <select id="cron_day" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                                <option value="*">Every</option>
                                @for($i=1;$i<=31;$i++)
                                    <option value="{{$i}}" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[2] ?? '*') == $i ? 'selected' : '' }}>{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Hour</label>
                            <select id="cron_hour" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                                <option value="*">Every</option>
                                @for($i=0;$i<24;$i++)
                                    <option value="{{$i}}" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[1] ?? '*') == $i ? 'selected' : '' }}>{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Minute</label>
                            <select id="cron_minute" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                                <option value="*">Every</option>
                                @for($i=0;$i<60;$i++)
                                    <option value="{{$i}}" {{ (explode(' ', old('cron_expression', $scheduledReport->cron_expression))[0] ?? '*') == $i ? 'selected' : '' }}>{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="cron_expression" id="cron_expression" value="{{ old('cron_expression', $scheduledReport->cron_expression) }}">
                    <span class="text-xs text-gray-500">Choose when the report should run. <a href='https://crontab.guru/' target='_blank' class='underline'>Learn more</a><br>
                    </span>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Status</label>
                    <select name="status" class="form-select w-full border border-teal-500 focus:border-teal-600 focus:ring-teal-500 px-2 py-1 rounded-sm bg-white">
                        <option value="active" {{ old('status', $scheduledReport->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ old('status', $scheduledReport->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Update Schedule</button>
        </div>
        <script>
        function updateCron() {
            const minute = document.getElementById('cron_minute').value;
            const hour = document.getElementById('cron_hour').value;
            const day = document.getElementById('cron_day').value;
            const month = document.getElementById('cron_month').value;
            const weekday = document.getElementById('cron_weekday').value;
            document.getElementById('cron_expression').value = `${minute} ${hour} ${day} ${month} ${weekday}`;
        }
        function toggleNotificationRecipients() {
            const enabled = document.getElementById('notifications_enabled').checked;
            document.getElementById('notification_recipients_group').style.display = enabled ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', function() {
            ['cron_minute','cron_hour','cron_day','cron_month','cron_weekday'].forEach(id => {
                document.getElementById(id).addEventListener('change', updateCron);
            });
            updateCron();
            document.getElementById('notifications_enabled').addEventListener('change', toggleNotificationRecipients);
            toggleNotificationRecipients();
        });
        </script>
    </form>
</div>
@endsection
