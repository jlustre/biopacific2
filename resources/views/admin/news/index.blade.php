@extends('layouts.dashboard', ['title' => 'News Management'])

@section('header')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">News Management</h1>
        <p class="text-gray-600 mt-2">Create and publish news articles for facility websites.</p>
    </div>
    <a href="{{ route('admin.news.create', $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : []) }}"
        class="inline-flex items-center justify-center bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-sm transition">
        <i class="fas fa-plus mr-2"></i> Add News Article
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-center">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(isset($scopedFacility) && $scopedFacility)
    <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
        Showing news for <strong>{{ $scopedFacility->name }}</strong> only.
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                <i class="fas fa-newspaper text-slate-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total articles</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Published</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['published'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-pencil-alt text-amber-600 text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Drafts</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['drafts'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.news.index') }}"
            class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search title or summary..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="w-full lg:w-36">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="w-full lg:w-36">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Scope</label>
                <select name="scope" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="global" {{ request('scope') === 'global' ? 'selected' : '' }}>Global</option>
                    <option value="local" {{ request('scope') === 'local' ? 'selected' : '' }}>Facility</option>
                </select>
            </div>
            @if(!empty($canFilterFacilities))
            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Facility</label>
                <select name="facility_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All facilities</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ (string) request('facility_id', $filterFacilityId ?? '') === (string) $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-semibold">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.news.index') }}"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- News grid --}}
    @if($news->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 px-6 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <i class="fas fa-newspaper text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No news articles found</h3>
        <p class="text-gray-500 max-w-md mx-auto mb-6">
            @if(request()->hasAny(['search', 'status', 'scope', 'facility_id']))
            Try adjusting your filters, or create a new article.
            @elseif(isset($scopedFacility))
            Get started by publishing news for {{ $scopedFacility->name }}.
            @else
            Get started by publishing your first news article.
            @endif
        </p>
        <a href="{{ route('admin.news.create', $scopedFacilityId ? ['facility_id' => $scopedFacilityId] : []) }}"
            class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-lg font-semibold">
            <i class="fas fa-plus mr-2"></i> Add News Article
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($news as $item)
        @php
            $imageUrl = $item->image ? asset('storage/' . $item->image) : null;
            $facilityNames = $item->facilities->pluck('name');
            if ($item->facility && !$facilityNames->contains($item->facility->name)) {
                $facilityNames = $facilityNames->prepend($item->facility->name);
            }
        @endphp
        <article class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
            <div class="relative h-40 bg-gradient-to-br from-slate-100 to-slate-200">
                @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-slate-300"></i>
                </div>
                @endif
                <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->status ? 'bg-green-500 text-white' : 'bg-gray-600 text-white' }}">
                        {{ $item->status ? 'Published' : 'Draft' }}
                    </span>
                    @if($item->is_global)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-500 text-white">Global</span>
                    @endif
                    @php
                        $visibilityBadge = match ($item->visibility ?? 'both') {
                            'website' => ['Website', 'bg-sky-500 text-white'],
                            'portal' => ['Portal', 'bg-violet-500 text-white'],
                            default => ['Both', 'bg-teal-600 text-white'],
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $visibilityBadge[1] }}">{{ $visibilityBadge[0] }}</span>
                </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="text-lg font-bold text-gray-900 leading-snug mb-2 line-clamp-2">{{ $item->title }}</h3>
                <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-1">
                    {{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 120) }}
                </p>
                <div class="space-y-2 text-xs text-gray-500 mb-4">
                    <div class="flex items-center gap-1.5">
                        <i class="far fa-calendar-alt text-gray-400"></i>
                        @if($item->published_at)
                        <span>{{ $item->published_at->format('M j, Y') }}</span>
                        <span class="text-gray-300">·</span>
                        <span>{{ $item->published_at->diffForHumans() }}</span>
                        @else
                        <span class="italic">Not scheduled</span>
                        @endif
                    </div>
                    @if($facilityNames->isNotEmpty())
                    <div class="flex items-start gap-1.5">
                        <i class="fas fa-building text-gray-400 mt-0.5"></i>
                        <span class="line-clamp-2">{{ $facilityNames->join(', ') }}</span>
                    </div>
                    @elseif(!$item->is_global)
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-building text-gray-400"></i>
                        <span class="italic">No facility assigned</span>
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.news.show', $item) }}"
                        class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                    @if(empty($scopedFacilityId) || !$item->is_global)
                    <a href="{{ route('admin.news.edit', $item) }}"
                        class="flex-1 text-center px-3 py-2 text-sm font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <form action="{{ route('admin.news.destroy', $item) }}" method="POST" class="inline"
                        onsubmit="return confirm('Delete this news article?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition"
                            title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @else
                    <span class="flex-1 text-center px-3 py-2 text-xs text-gray-400">Global (read-only)</span>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>

    @if($news->hasPages())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3">
        {{ $news->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
