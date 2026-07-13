@extends('layouts.member-portal')

@php
    $articles = collect($newsArticles ?? []);
    $totalCount = $articles->count();
    $companyCount = (int) ($newsCompanyCount ?? $articles->where('is_global', true)->count());
    $facilityCount = (int) ($newsFacilityCount ?? $articles->where('is_global', false)->count());
    $facilityLabel = $newsFacilityLabel ?? ($facilityName ?? null);
@endphp

@push('head')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('newsEventsPage', (articles = []) => ({
        articles,
        filter: 'all',
        query: '',
        selectedId: null,
        get filtered() {
            const q = this.query.trim().toLowerCase();
            return this.articles.filter((item) => {
                if (this.filter === 'company' && !item.is_global) return false;
                if (this.filter === 'facility' && item.is_global) return false;
                if (!q) return true;
                return [item.title, item.summary, item.content, item.facility_name]
                    .filter(Boolean)
                    .some((value) => String(value).toLowerCase().includes(q));
            });
        },
        get featured() {
            return this.filtered[0] ?? null;
        },
        get rest() {
            return this.filtered.slice(1);
        },
        get selected() {
            return this.articles.find((item) => item.id === this.selectedId) ?? null;
        },
        openArticle(id) {
            this.selectedId = id;
            document.body.classList.add('overflow-hidden');
        },
        closeArticle() {
            this.selectedId = null;
            document.body.classList.remove('overflow-hidden');
        },
        formatBody(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');
        },
    }));
});
</script>
@endpush

@section('content')
<section
    class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5"
    x-data="newsEventsPage(@js($articles))"
    @keydown.escape.window="if (selectedId) closeArticle()"
>
    {{-- Header --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="relative border-b border-slate-200 bg-gradient-to-br from-teal-50 via-white to-slate-50 px-4 py-5 sm:px-6 sm:py-6">
            <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-teal-200/30 blur-2xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-1/3 h-24 w-24 rounded-full bg-cyan-100/40 blur-2xl"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-teal-100/90 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-teal-800">
                        <i class="fa-solid fa-newspaper"></i>
                        Company News / Events
                    </div>
                    <h1 class="mt-3 text-xl font-black tracking-tight text-slate-900 sm:text-2xl">
                        Stay current with Bio Pacific
                    </h1>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                        Company-wide announcements and
                        @if(filled($facilityLabel) && $facilityLabel !== '—')
                            updates for <span class="font-semibold text-slate-800">{{ $facilityLabel }}</span>.
                        @else
                            facility updates for your team.
                        @endif
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="min-w-[4.5rem] rounded-xl border border-slate-200 bg-white/90 px-3 py-2 text-center shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">All</p>
                        <p class="mt-0.5 text-lg font-black text-slate-900">{{ $totalCount }}</p>
                    </div>
                    <div class="min-w-[4.5rem] rounded-xl border border-emerald-100 bg-emerald-50/80 px-3 py-2 text-center shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">Company</p>
                        <p class="mt-0.5 text-lg font-black text-emerald-900">{{ $companyCount }}</p>
                    </div>
                    <div class="min-w-[4.5rem] rounded-xl border border-teal-100 bg-teal-50/80 px-3 py-2 text-center shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-teal-700">Facility</p>
                        <p class="mt-0.5 text-lg font-black text-teal-900">{{ $facilityCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="filter = 'all'"
                        :class="filter === 'all' ? 'bg-teal-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:bg-teal-50'"
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold transition">
                    All updates
                    <span class="rounded-full px-1.5 py-0.5 text-[10px]"
                          :class="filter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">{{ $totalCount }}</span>
                </button>
                <button type="button" @click="filter = 'company'"
                        :class="filter === 'company' ? 'bg-teal-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:bg-teal-50'"
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold transition">
                    Company-wide
                    <span class="rounded-full px-1.5 py-0.5 text-[10px]"
                          :class="filter === 'company' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">{{ $companyCount }}</span>
                </button>
                <button type="button" @click="filter = 'facility'"
                        :class="filter === 'facility' ? 'bg-teal-600 text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-700 hover:border-teal-300 hover:bg-teal-50'"
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold transition">
                    Facility
                    <span class="rounded-full px-1.5 py-0.5 text-[10px]"
                          :class="filter === 'facility' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'">{{ $facilityCount }}</span>
                </button>
            </div>
            <label class="relative block w-full sm:max-w-xs">
                <span class="sr-only">Search news</span>
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search"
                       x-model.debounce.200ms="query"
                       placeholder="Search titles or details…"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-teal-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-200">
            </label>
        </div>

        <div class="p-4 sm:p-6">
            {{-- Empty: no published items at all --}}
            @if($totalCount === 0)
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-slate-200">🗞️</div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">No news or events yet</h2>
                    <p class="mx-auto mt-1.5 max-w-md text-sm text-slate-500">
                        Company and facility announcements will appear here when they are published.
                    </p>
                    <a href="{{ route('dashboard.index') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-teal-700">
                        Return to dashboard
                    </a>
                </div>
            @else
                {{-- Empty: filter/search --}}
                <template x-if="filtered.length === 0">
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-xl shadow-sm ring-1 ring-slate-200">🔍</div>
                        <p class="mt-4 text-sm font-semibold text-slate-800">No matching updates</p>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">Try another filter or clear your search.</p>
                        <button type="button"
                                @click="filter = 'all'; query = ''"
                                class="mt-4 text-sm font-semibold text-teal-700 hover:underline">
                            Reset filters
                        </button>
                    </div>
                </template>

                <template x-if="filtered.length > 0">
                    <div class="space-y-6">
                        {{-- Featured --}}
                        <article
                            class="group cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 text-white shadow-sm transition hover:shadow-md"
                            @click="openArticle(featured.id)"
                        >
                            <div class="grid lg:grid-cols-5">
                                <div class="relative min-h-[14rem] overflow-hidden lg:col-span-2 lg:min-h-full">
                                    <template x-if="featured.image_url">
                                        <img :src="featured.image_url" :alt="featured.title"
                                             class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    </template>
                                    <template x-if="!featured.image_url">
                                        <div class="absolute inset-0 bg-gradient-to-br from-teal-700 via-teal-800 to-slate-900">
                                            <div class="absolute inset-0 opacity-30"
                                                 style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35), transparent 45%), radial-gradient(circle at 80% 70%, rgba(45,212,191,.45), transparent 40%);"></div>
                                            <div class="relative flex h-full min-h-[14rem] items-center justify-center">
                                                <span class="text-5xl opacity-80">📰</span>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent lg:hidden"></div>
                                </div>
                                <div class="flex flex-col justify-center gap-3 p-5 sm:p-7 lg:col-span-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                              :class="featured.is_global ? 'bg-emerald-400/20 text-emerald-200' : 'bg-teal-400/20 text-teal-100'"
                                              x-text="featured.scope_label"></span>
                                        <span class="text-xs text-slate-300" x-text="featured.facility_name"></span>
                                        <span class="text-xs text-slate-400" x-show="featured.published_label" x-text="'· ' + featured.published_label"></span>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-teal-300">Latest update</p>
                                        <h2 class="mt-1 text-xl font-black leading-snug tracking-tight sm:text-2xl" x-text="featured.title"></h2>
                                    </div>
                                    <p class="text-sm leading-relaxed text-slate-300 line-clamp-3" x-text="featured.summary"></p>
                                    <div class="mt-1 flex items-center gap-2 text-sm font-bold text-teal-300 transition group-hover:text-teal-200">
                                        Read full update
                                        <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-0.5"></i>
                                    </div>
                                </div>
                            </div>
                        </article>

                        {{-- Remaining grid --}}
                        <div x-show="rest.length > 0" class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-bold text-slate-800">More updates</h3>
                                <p class="text-xs text-slate-500">
                                    <span x-text="filtered.length"></span>
                                    <span x-text="filtered.length === 1 ? 'item' : 'items'"></span>
                                    in view
                                </p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                <template x-for="item in rest" :key="item.id">
                                    <article
                                        class="group flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md"
                                        @click="openArticle(item.id)"
                                    >
                                        <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                                            <template x-if="item.image_url">
                                                <img :src="item.image_url" :alt="item.title"
                                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                                            </template>
                                            <template x-if="!item.image_url">
                                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 via-teal-50 to-slate-200">
                                                    <span class="text-3xl opacity-70">🗞️</span>
                                                </div>
                                            </template>
                                            <span class="absolute left-3 top-3 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide shadow-sm"
                                                  :class="item.is_global ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800'"
                                                  x-text="item.scope_label"></span>
                                        </div>
                                        <div class="flex flex-1 flex-col p-4">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500">
                                                <span class="font-semibold text-slate-600" x-text="item.facility_name"></span>
                                                <span x-show="item.published_label" x-text="'· ' + item.published_label"></span>
                                            </div>
                                            <h3 class="mt-1.5 line-clamp-2 text-base font-bold leading-snug text-slate-900" x-text="item.title"></h3>
                                            <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-slate-600" x-text="item.excerpt"></p>
                                            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                                <span class="text-[11px] text-slate-400" x-text="item.published_relative || ''"></span>
                                                <span class="text-xs font-bold text-teal-700">Read →</span>
                                            </div>
                                        </div>
                                    </article>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            @endif
        </div>
    </div>

    {{-- Detail drawer --}}
    <div x-show="selectedId"
         x-cloak
         class="fixed inset-0 z-50"
         role="dialog"
         aria-modal="true"
         aria-labelledby="news-detail-title">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[1px]"
             x-show="selectedId"
             x-transition.opacity
             @click="closeArticle()"></div>

        <div class="absolute inset-y-0 right-0 flex w-full max-w-xl"
             x-show="selectedId"
             x-transition:enter="transition transform ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition transform ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            <template x-if="selected">
                <div class="flex h-full w-full flex-col bg-white shadow-2xl">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                      :class="selected.is_global ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800'"
                                      x-text="selected.scope_label"></span>
                                <span class="text-xs text-slate-500" x-text="selected.facility_name"></span>
                            </div>
                            <h2 id="news-detail-title" class="mt-2 text-lg font-black leading-snug text-slate-900" x-text="selected.title"></h2>
                            <p class="mt-1 text-xs text-slate-500">
                                <span x-show="selected.published_label" x-text="selected.published_label"></span>
                                <span x-show="selected.published_relative" x-text="' · ' + selected.published_relative"></span>
                            </p>
                        </div>
                        <button type="button"
                                @click="closeArticle()"
                                class="rounded-xl border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
                                aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto">
                        <template x-if="selected.image_url">
                            <div class="aspect-[16/9] w-full overflow-hidden bg-slate-100">
                                <img :src="selected.image_url" :alt="selected.title" class="h-full w-full object-cover">
                            </div>
                        </template>
                        <div class="space-y-4 px-5 py-5">
                            <p class="rounded-xl border border-teal-100 bg-teal-50/70 px-4 py-3 text-sm font-medium leading-relaxed text-teal-950"
                               x-show="selected.summary"
                               x-text="selected.summary"></p>
                            <div class="prose prose-sm max-w-none text-slate-700 prose-p:leading-relaxed"
                                 x-html="formatBody(selected.content)"></div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 px-5 py-3">
                        <button type="button"
                                @click="closeArticle()"
                                class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
@endsection
