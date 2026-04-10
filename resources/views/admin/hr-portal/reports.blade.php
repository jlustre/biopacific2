@extends('layouts.dashboard')
@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Available Reports</h1>
    @if($isAdmin)
        <div class="mb-4">
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-teal-600 text-white rounded">Go to Reports Management</a>
        </div>
    @else
        <div class="mb-4">
            <button type="button" id="request-report-btn" class="px-4 py-2 bg-orange-600 text-white rounded">Request a Report Template</button>
        </div>
        <!-- Modal Popup for Report Request -->
        <div id="request-report-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
            <div class="bg-white p-6 rounded shadow w-full max-w-md relative">
                <button type="button" id="close-request-modal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>
                <h2 class="text-lg font-bold mb-4">Request a Report Template</h2>
                <form id="report-request-form" method="POST" action="{{ route('admin.reports.request') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block font-semibold mb-1">Your Name</label>
                        <input type="text" name="user_name" class="form-input w-full border border-teal-500 px-2 py-1" value="{{ auth()->user()->name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="block font-semibold mb-1">Your Email</label>
                        <input type="email" name="user_email" class="form-input w-full border border-teal-500 px-2 py-1" value="{{ auth()->user()->email ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="block font-semibold mb-1">Report Title/Name</label>
                        <input type="text" name="report_title" class="form-input w-full border border-teal-500 px-2 py-1" required>
                    </div>
                    <div class="mb-3">
                        <label class="block font-semibold mb-1">Report Description / Purpose</label>
                        <textarea name="report_description" class="form-input w-full border border-teal-500 px-2 py-1" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="block font-semibold mb-1">Sample Data Columns (optional)</label>
                        <input type="text" name="sample_columns" class="form-input w-full border border-teal-500 px-2 py-1" placeholder="e.g. id, name, date, ...">
                    </div>
                    <div class="mb-3">
                        <label class="block font-semibold mb-1">Additional Notes (optional)</label>
                        <textarea name="notes" class="form-input w-full border border-teal-500 px-2 py-1" rows="2"></textarea>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" id="cancel-request-btn" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Send Request</button>
                    </div>
                </form>
                <div id="request-success-message" class="hidden mt-4 text-green-700 font-semibold"></div>
            </div>
        </div>
    @endif
    <div class="p-6 bg-white rounded shadow">
        @if($reports->isEmpty())
            <div class="text-gray-600">No reports available for your account.</div>
        @else
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">Name</th>
                    <th class="px-3 py-2 border">Description</th>
                    <th class="px-3 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td class="px-3 py-2 border">{{ $report->name }}</td>
                    <td class="px-3 py-2 border">{{ $report->description }}</td>
                    <td class="px-3 py-2 border">
                        <a href="#" class="px-3 py-1 bg-blue-600 text-white rounded run-report-btn" data-id="{{ $report->id }}">Run</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    <!-- Modal for running reports can be added here if needed -->
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('request-report-modal');
            const openBtn = document.getElementById('request-report-btn');
            const closeBtn = document.getElementById('close-request-modal');
            const cancelBtn = document.getElementById('cancel-request-btn');
            const form = document.getElementById('report-request-form');
            const successMsg = document.getElementById('request-success-message');
            if (openBtn) openBtn.onclick = () => { modal.classList.remove('hidden'); };
            if (closeBtn) closeBtn.onclick = () => { modal.classList.add('hidden'); };
            if (cancelBtn) cancelBtn.onclick = () => { modal.classList.add('hidden'); };
            // AJAX submit for the form
            if (form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('[name=_token]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            form.classList.add('hidden');
                            successMsg.textContent = data.message || 'Your request has been sent to the admin.';
                            successMsg.classList.remove('hidden');
                        } else {
                            alert(data.message || 'Failed to send request.');
                        }
                    })
                    .catch(() => {
                        alert('Failed to send request.');
                    });
                };
            }
        });
    </script>
@endsection
