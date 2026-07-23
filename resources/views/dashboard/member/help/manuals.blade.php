@extends('layouts.member-portal')

@section('content')
@php
    $filters = $manualFilters ?? ['q' => '', 'category' => '', 'per_page' => 10];
    $hasActiveFilters = filled($filters['q'] ?? null) || filled($filters['category'] ?? null);
    $availableCount = (int) ($manualTotalCount ?? ($manuals->total() ?? 0));
@endphp
<section class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
    @include('dashboard.member.help.partials.hero', [
        'tone' => 'teal',
        'heroIcon' => 'fa-book-open',
        'heroBadge' => 'Self-service',
        'heroTitle' => 'Manuals and Docs',
        'heroSubtitle' => 'User guides, workflow references, and manuals for the employee and HR portals. Open any document as a PDF.',
        'tips' => [
            ['icon' => 'fa-bookmark', 'title' => 'Start here', 'body' => 'Search or filter the library, then open View PDF for the full guide.'],
            ['icon' => 'fa-diagram-project', 'title' => 'Workflows', 'body' => 'Part G competency and other process guides are listed under the Workflows category.'],
            ['icon' => 'fa-headset', 'title' => 'Still stuck?', 'body' => 'Contact HR for payroll and benefits, or Technical Support for portal issues.'],
        ],
        'stats' => [
            ['label' => 'Library', 'value' => $availableCount.' document'.($availableCount === 1 ? '' : 's')],
            ['label' => 'Access', 'value' => 'Self-service'],
            ['label' => 'Format', 'value' => 'View as PDF'],
        ],
    ])

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('member.help.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">View my help requests</a>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('member.help.hr') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-headset text-xs"></i>
                Contact HR
            </a>
            <a href="{{ route('member.help.support') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-laptop-code text-xs"></i>
                Technical Support
            </a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4 sm:px-8">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-teal-700">Library</p>
                    <h2 class="mt-1 text-base font-bold text-slate-900">User guides, workflows & manuals</h2>
                    <p class="mt-1 text-sm text-slate-500">Search titles, categories, or full document contents, then open a guide as PDF.</p>
                </div>
                <span class="rounded-full bg-teal-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-teal-800">
                    {{ $availableCount }} available
                </span>
            </div>
        </div>

        <div class="px-6 py-5 sm:px-8">
            <form method="GET" action="{{ route('member.help.manuals') }}" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="lg:col-span-3">
                    <label for="manuals-search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                    <input
                        type="search"
                        id="manuals-search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Search titles, categories, or document contents…"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100"
                    >
                </div>
                <div>
                    <label for="manuals-category" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Category</label>
                    <select
                        id="manuals-category"
                        name="category"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100"
                    >
                        <option value="">All categories</option>
                        @foreach(($manualCategories ?? []) as $categoryOption)
                            <option value="{{ $categoryOption }}" @selected(($filters['category'] ?? '') === $categoryOption)>{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="manuals-per-page" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Per page</label>
                    <select
                        id="manuals-per-page"
                        name="per_page"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100"
                    >
                        @foreach([10, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-end gap-2 lg:col-span-6">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800">
                        <i class="fa-solid fa-filter text-xs" aria-hidden="true"></i>
                        Apply
                    </button>
                    @if($hasActiveFilters)
                        <a href="{{ route('member.help.manuals') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Reset</a>
                    @endif
                    @if($manuals->total() > 0)
                        <p class="text-xs text-slate-500 sm:ml-auto">
                            Showing {{ $manuals->firstItem() }}–{{ $manuals->lastItem() }} of {{ $manuals->total() }}
                            @if($hasActiveFilters)
                                matching
                            @endif
                        </p>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Document</th>
                            <th scope="col" class="px-4 py-3">Category</th>
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($manuals as $manual)
                            <tr class="align-top hover:bg-slate-50/80">
                                <td class="px-4 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                                            <i class="fa-solid {{ $manual['icon'] ?? 'fa-book' }}"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $manual['title'] }}</p>
                                            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">PDF</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-700">
                                        {{ $manual['category'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    <p>{{ $manual['description'] }}</p>
                                    @if(! empty($manual['match_snippet']))
                                        <p class="mt-2 rounded-lg border border-teal-100 bg-teal-50/70 px-2.5 py-2 text-xs leading-relaxed text-teal-900">
                                            <span class="font-semibold uppercase tracking-wide text-teal-700">In document:</span>
                                            {{ $manual['match_snippet'] }}
                                        </p>
                                    @elseif(! empty($manual['match_in_content']))
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-teal-700">Matched in document contents</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    @php
                                        $pdfParams = ['document' => $manual['key']];
                                        $searchQuery = trim((string) ($filters['q'] ?? ''));
                                        if ($searchQuery !== '') {
                                            $pdfParams['q'] = $searchQuery;
                                        }
                                        $pdfUrl = route('member.help.document', $pdfParams);
                                        if ($searchQuery !== '') {
                                            // Built-in PDF viewers (Chrome/Edge) jump to the first match.
                                            $pdfUrl .= '#search='.rawurlencode($searchQuery);
                                        }
                                    @endphp
                                    <a
                                        href="{{ $pdfUrl }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-black"
                                    >
                                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                                        View PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">
                                    @if($hasActiveFilters)
                                        No manuals match your search or filters.
                                        <a href="{{ route('member.help.manuals') }}" class="font-semibold text-teal-700 hover:text-teal-900">Clear filters</a>
                                    @else
                                        Manuals and workflow documents will appear here when published.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($manuals->hasPages())
                <div class="mt-5">
                    {{ $manuals->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
