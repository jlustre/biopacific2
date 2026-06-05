@extends('layouts.member-portal')

@push('head')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('facilitiesWebsitesPage', () => ({
        open: false,
        loading: false,
        openingWebsite: false,
        openingWebsiteLabel: '',
        error: null,
        detail: null,
        async openDetail(id, url) {
            this.open = true;
            this.loading = true;
            this.error = null;
            this.detail = null;
            document.body.classList.add('overflow-hidden');
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    throw new Error('Could not load facility details.');
                }
                this.detail = await response.json();
            } catch (e) {
                this.error = e.message || 'Could not load facility details.';
            } finally {
                this.loading = false;
            }
        },
        closeDetail() {
            this.open = false;
            this.detail = null;
            this.error = null;
            document.body.classList.remove('overflow-hidden');
        },
        openWebsite(url, label = '') {
            if (!url || this.openingWebsite) {
                return;
            }

            this.openingWebsite = true;
            this.openingWebsiteLabel = label || 'facility website';

            const newWindow = window.open(url, '_blank', 'noopener,noreferrer');

            if (!newWindow) {
                window.location.href = url;
            }

            window.setTimeout(() => {
                this.openingWebsite = false;
                this.openingWebsiteLabel = '';
            }, 1400);
        },
    }));
});
</script>
@endpush

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-black text-slate-900 sm:text-2xl">Facilities Websites</h1>
                <p class="mt-1 text-sm text-slate-600">Browse facility websites, contact details, and leadership rosters.</p>
            </div>
            <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-800">{{ $facilities->count() }} facilities</span>
        </div>

        @if($facilities->isEmpty())
        <p class="mt-8 text-center text-sm text-slate-500">No active facilities are available right now.</p>
        @else
        <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Facility</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Website</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($facilities as $facility)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $facility['name'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $facility['location'] }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            @if(filled($facility['phone']))
                            <a href="tel:{{ preg_replace('/\D+/', '', $facility['phone']) }}" class="text-teal-700 hover:text-teal-900">{{ $facility['phone'] }}</a>
                            @else
                            —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(filled($facility['website_url']))
                            <button type="button"
                                    onclick="window.dispatchEvent(new CustomEvent('open-facility-website', { detail: { url: @js($facility['website_url']), label: @js($facility['website_label'] ?? $facility['domain']) } }))"
                                    class="font-semibold text-teal-700 underline decoration-teal-200 underline-offset-2 hover:text-teal-900">
                                {{ $facility['website_label'] ?? $facility['domain'] }}
                            </button>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                    onclick="window.dispatchEvent(new CustomEvent('open-facility-detail', { detail: { id: {{ $facility['id'] }}, url: @js($facility['detail_url']) } }))"
                                    class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">
                                View
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</section>

<div x-data="facilitiesWebsitesPage"
     x-on:open-facility-detail.window="openDetail($event.detail.id, $event.detail.url)"
     x-on:open-facility-website.window="openWebsite($event.detail.url, $event.detail.label)">
    <div x-show="openingWebsite"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 p-4"
         role="status"
         aria-live="polite"
         aria-busy="true">
        <div class="flex flex-col items-center gap-3 rounded-2xl bg-white px-6 py-5 shadow-2xl">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-50 text-teal-700">
                <i class="fa-solid fa-spinner fa-spin text-lg" aria-hidden="true"></i>
            </span>
            <div class="text-center">
                <p class="text-sm font-bold text-slate-900">Opening website</p>
                <p class="mt-1 text-xs text-slate-500" x-text="openingWebsiteLabel"></p>
            </div>
        </div>
    </div>

    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         role="dialog"
         aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/50" x-on:click="closeDetail()"></div>
    <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl"
         x-on:click.stop
         x-on:keydown.escape.window="closeDetail()">
        <div class="sticky top-0 flex items-center justify-between border-b border-slate-100 bg-white px-5 py-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Facility details</p>
                <h2 class="text-lg font-black text-slate-900" x-text="detail?.facility?.name ?? 'Loading…'"></h2>
            </div>
            <button type="button" x-on:click="closeDetail()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Close">✕</button>
        </div>

        <div class="px-5 py-4">
            <template x-if="loading">
                <div class="flex flex-col items-center gap-3 py-10">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-50 text-teal-700">
                        <i class="fa-solid fa-spinner fa-spin text-lg" aria-hidden="true"></i>
                    </span>
                    <p class="text-sm text-slate-500">Loading facility details…</p>
                </div>
            </template>

            <template x-if="error">
                <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800" x-text="error"></p>
            </template>

            <template x-if="detail && !loading">
                <div class="space-y-5">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Location</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900" x-text="detail.facility.location"></p>
                            <p class="mt-1 text-xs text-slate-600" x-show="detail.facility.address" x-text="detail.facility.address"></p>
                            <p class="text-xs text-slate-600" x-show="detail.facility.city || detail.facility.state">
                                <span x-text="[detail.facility.city, detail.facility.state, detail.facility.zip].filter(Boolean).join(', ')"></span>
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Contact</p>
                            <p class="mt-1 text-sm text-slate-800" x-show="detail.facility.phone">
                                Phone: <span class="font-semibold" x-text="detail.facility.phone"></span>
                            </p>
                            <p class="mt-1 text-sm text-slate-800" x-show="detail.facility.email">
                                Email: <span class="font-semibold" x-text="detail.facility.email"></span>
                            </p>
                            <p class="mt-1 text-sm" x-show="detail.facility.website_url">
                                <button type="button"
                                        x-on:click.stop="openWebsite(detail.facility.website_url, detail.facility.website_label || detail.facility.domain)"
                                        class="font-semibold text-teal-700 underline decoration-teal-200 underline-offset-2 hover:text-teal-900"
                                        x-text="detail.facility.website_label || detail.facility.domain"></button>
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2" x-show="detail.facility.facility_number || detail.facility.region || detail.facility.beds">
                        <template x-if="detail.facility.facility_number">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Facility #</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900" x-text="detail.facility.facility_number"></p>
                            </div>
                        </template>
                        <template x-if="detail.facility.region">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Region</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900" x-text="detail.facility.region"></p>
                            </div>
                        </template>
                        <template x-if="detail.facility.beds">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Beds</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900" x-text="detail.facility.beds"></p>
                            </div>
                        </template>
                        <template x-if="detail.facility.hours">
                            <div class="rounded-xl border border-slate-200 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Hours</p>
                                <p class="mt-1 text-sm text-slate-800" x-text="detail.facility.hours"></p>
                            </div>
                        </template>
                    </div>

                    <template x-if="detail.facility.about_text">
                        <div>
                            <h3 class="text-sm font-black text-slate-900">About</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600" x-text="detail.facility.about_text"></p>
                        </div>
                    </template>

                    <div>
                        <h3 class="text-sm font-black text-slate-900">Leadership</h3>
                        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2">Role</th>
                                        <th class="px-3 py-2">Name</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="row in detail.leadership" :key="row.key">
                                        <tr>
                                            <td class="px-3 py-2 text-slate-600">
                                                <span class="font-semibold text-slate-800" x-text="row.office"></span>
                                                <span class="ml-1 text-xs text-slate-400" x-text="row.abbrev"></span>
                                            </td>
                                            <td class="px-3 py-2 font-semibold" :class="row.vacant ? 'text-slate-400' : 'text-slate-900'" x-text="row.name"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
    </div>
</div>
@endsection
