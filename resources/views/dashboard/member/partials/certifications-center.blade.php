@php
    $certificationsCenter = $certificationsCenter ?? [];
    $items = $certificationsCenter['items'] ?? [];
    $hasEmployeeRecord = $certificationsCenter['has_employee_record'] ?? false;
    $positionTitle = $certificationsCenter['position_title'] ?? null;
    $employmentPortalUrl = \Illuminate\Support\Facades\Route::has('employment.portal') ? route('employment.portal') : '#';
    $documentsPageUrl = \Illuminate\Support\Facades\Route::has('member.documents')
        ? route('member.documents')
        : $employmentPortalUrl;
    $requiredUploadTypes = collect($certificationsCenter['required_upload_types'] ?? [])->values();
    $submissionReasonOptions = $certificationsCenter['submission_reason_options'] ?? \App\Support\UploadSubmissionReason::options();
    $canUploadCredentials = $hasEmployeeRecord && $requiredUploadTypes->isNotEmpty();
@endphp

<section id="certifications"
         class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card"
         x-data="memberCredentialsCenter({
            uploadUrl: @js(route('employment.documents.upload')),
            uploadTypes: @js($requiredUploadTypes),
            submissionReasons: @js($submissionReasonOptions),
            csrf: @js(csrf_token())
         })"
         x-init="initFromQuery()">
    <div class="border-b border-slate-200 bg-teal-50 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-teal-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                    <i class="fa-solid fa-certificate"></i>
                    My Credentials
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">My Credentials</h2>
                <p class="mt-1 text-sm text-slate-500">
                    @if($positionTitle)
                        Required licenses and certifications for <span class="font-semibold text-slate-700">{{ $positionTitle }}</span>
                    @else
                        Required licenses and certifications for your position
                    @endif
                    <span class="block mt-1 text-xs text-slate-500">
                        {{ config('documents.labels.certifications_subset_note') }}
                        <a href="{{ $documentsPageUrl }}" class="font-semibold text-brand-600 hover:text-brand-700">View all position documents</a>
                        — uploads are reviewed by your facility DSD, DON, or administrator.
                    </span>
                </p>
            </div>
            @if($hasEmployeeRecord)
                <div class="flex flex-wrap items-center gap-2">
                    @if($canUploadCredentials)
                        <button type="button"
                                @click="openUploadModal()"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-bold text-brand-700 hover:bg-brand-50">
                            <i class="fa-solid fa-upload"></i>
                            Upload credential
                        </button>
                    @endif
                    <a href="{{ $employmentPortalUrl }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                        <i class="fa-solid fa-briefcase"></i>
                        Employment portal
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="p-6">
        @unless($hasEmployeeRecord)
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-2xl text-slate-500">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-950">No employee record linked</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Your account is not linked to an employee file yet. Contact HR if you believe this is an error.</p>
            </div>
        @else
            @if(count($items) === 0)
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-100 text-2xl text-teal-700">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-950">No licenses or certifications for your position</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">When licenses or credentials apply to your role, they will appear here with status and renewal dates from your employee file.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Credential</th>
                                <th class="px-4 py-3">Required</th>
                                <th class="px-4 py-3">Expiry</th>
                                <th class="px-4 py-3">Days</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="w-20 whitespace-nowrap px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $cert)
                                @php
                                    $rowClass = match ($cert['status'] ?? '') {
                                        'expired', 'expires_today', 'expiring_urgent' => 'bg-rose-50/40',
                                        'expiring_soon' => 'bg-amber-50/40',
                                        default => '',
                                    };
                                    $daysLabel = isset($cert['days_until'])
                                        ? ($cert['days_until'] < 0
                                            ? abs($cert['days_until']) . 'd ago'
                                            : ($cert['days_until'] === 0 ? 'Today' : $cert['days_until'] . 'd'))
                                        : '—';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-950">{{ $cert['title'] ?? '—' }}</p>
                                        @if(!empty($cert['doc_type']))
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $cert['doc_type'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ !empty($cert['required']) ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $cert['exp_dt_formatted'] ?? '—' }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $daysLabel }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $cert['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $cert['status_label'] ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        @if(!empty($cert['upload_type_id']))
                                            <button type="button"
                                                    @click="openUploadModal(@js($cert['upload_type_id']))"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700"
                                                    title="Upload credential"
                                                    aria-label="Upload credential">
                                                <i class="fa-solid fa-upload"></i>
                                            </button>
                                        @else
                                            <span class="text-slate-300" title="No upload type configured">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endunless
    </div>

    <template x-teleport="body">
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
            <div class="absolute inset-0 bg-black/50" @click="closeUploadModal()"></div>
            <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Upload credential</h3>
                    <button type="button" class="text-xl leading-none text-slate-500 hover:text-slate-700" @click="closeUploadModal()" aria-label="Close">&times;</button>
                </div>
                <form method="POST" :action="uploadUrl" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="redirect_to" value="member.certifications">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold">Credential type <span class="text-rose-600">*</span></label>
                            <select name="upload_type_id" x-model="form.upload_type_id" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">Select credential type</option>
                                <template x-for="group in uploadTypeGroups" :key="group.section">
                                    <optgroup :label="group.section">
                                        <template x-for="type in group.items" :key="type.id">
                                            <option :value="String(type.id)" x-text="type.name"></option>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold">File <span class="text-rose-600">*</span></label>
                            <input type="file" name="document" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold">{{ config('documents.labels.submission_reason') }} <span class="text-rose-600">*</span></label>
                        <select name="submission_reason" x-model="form.submission_reason" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">Select a reason…</option>
                            <template x-for="(label, key) in submissionReasons" :key="key">
                                <option :value="key" x-text="label"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">{{ config('documents.labels.upload_review_notice') }}</p>
                    </div>

                    <div x-show="requiresExpiry" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold">Effective Start Date</label>
                            <input type="date" name="effective_start_date" x-model="form.effective_start_date" class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold">Expires At <span class="text-rose-600">*</span></label>
                            <input type="date" name="expires_at" x-model="form.expires_at" :required="requiresExpiry" :min="form.effective_start_date || null" class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold">Comments</label>
                        <textarea name="comments" rows="2" x-model="form.comments" class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="closeUploadModal()" class="rounded bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">Cancel</button>
                        <button type="submit" class="rounded bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</section>

<script>
    function memberCredentialsCenter(config) {
        return {
            showUploadModal: false,
            uploadUrl: config.uploadUrl,
            uploadTypes: config.uploadTypes || [],
            submissionReasons: config.submissionReasons || {},
            csrf: config.csrf,
            form: {
                upload_type_id: '',
                submission_reason: '',
                effective_start_date: '',
                expires_at: '',
                comments: '',
            },
            get uploadTypeGroups() {
                const groups = {};
                for (const type of this.uploadTypes) {
                    const section = type.section || 'Credentials';
                    if (!groups[section]) {
                        groups[section] = { section, items: [] };
                    }
                    groups[section].items.push(type);
                }
                return Object.values(groups);
            },
            get requiresExpiry() {
                const selected = this.uploadTypes.find((type) => String(type.id) === String(this.form.upload_type_id));
                return !!(selected && selected.requires_expiry);
            },
            openUploadModal(uploadTypeId = null) {
                this.form.upload_type_id = uploadTypeId ? String(uploadTypeId) : '';
                this.form.submission_reason = '';
                this.form.effective_start_date = '';
                this.form.expires_at = '';
                this.form.comments = '';
                this.showUploadModal = true;
            },
            initFromQuery() {
                const uploadTypeId = new URLSearchParams(window.location.search).get('upload_type_id');
                if (uploadTypeId) {
                    this.openUploadModal(uploadTypeId);
                }
            },
            closeUploadModal() {
                this.showUploadModal = false;
            },
        };
    }
</script>
