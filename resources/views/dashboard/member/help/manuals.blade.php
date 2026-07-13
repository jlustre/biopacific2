@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    @include('dashboard.member.help.partials.hero', [
        'tone' => 'teal',
        'heroIcon' => 'fa-book-open',
        'heroBadge' => 'Self-service',
        'heroTitle' => 'Manuals and Docs',
        'heroSubtitle' => 'User guides and reference documents for using the employee portal. More guides will be added here over time.',
        'tips' => [
            ['icon' => 'fa-bookmark', 'title' => 'Start here', 'body' => 'Open a guide below when you need a quick how-to for common portal tasks.'],
            ['icon' => 'fa-headset', 'title' => 'Still stuck?', 'body' => 'Contact HR for payroll and benefits, or Technical Support for portal and website issues.'],
            ['icon' => 'fa-clock', 'title' => 'Coming soon', 'body' => 'Guides marked Coming soon will unlock as documentation is published.'],
        ],
        'stats' => [
            ['label' => 'Type', 'value' => 'Guides · References'],
            ['label' => 'Access', 'value' => 'Self-service'],
            ['label' => 'Need help?', 'value' => 'HR or Tech Support'],
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
                    <h2 class="mt-1 text-base font-bold text-slate-900">User guides & manuals</h2>
                    <p class="mt-1 text-sm text-slate-500">Quick references for using the portal. More guides will be added here over time.</p>
                </div>
                @php
                    $publishedCount = collect($userGuides ?? [])->filter(fn ($guide) => ! empty($guide['url']))->count();
                @endphp
                @if($publishedCount === 0)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-600">Coming soon</span>
                @else
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-teal-800">{{ $publishedCount }} available</span>
                @endif
            </div>
        </div>
        <div class="grid gap-3 px-6 py-6 sm:grid-cols-3 sm:px-8">
            @forelse(($userGuides ?? []) as $guide)
                @if(!empty($guide['url']))
                    <a href="{{ $guide['url'] }}" target="_blank" rel="noopener"
                       class="rounded-2xl border border-teal-200 bg-teal-50/50 p-4 transition hover:border-teal-400 hover:bg-teal-50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                            <i class="fa-solid {{ $guide['icon'] ?? 'fa-book' }}"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900">{{ $guide['title'] }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $guide['description'] }}</p>
                        <p class="mt-3 text-xs font-semibold text-teal-700">Open guide →</p>
                    </a>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <i class="fa-solid {{ $guide['icon'] ?? 'fa-book' }}"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-800">{{ $guide['title'] }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $guide['description'] }}</p>
                        <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Coming soon</p>
                    </div>
                @endif
            @empty
                <div class="sm:col-span-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    User guides and manuals will be published here soon.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
