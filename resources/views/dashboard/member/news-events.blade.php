@extends('layouts.member-portal')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
  @if($newsItems->isEmpty())
  <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-card">
    <p class="text-4xl">📰</p>
    <p class="mt-3 text-lg font-semibold text-slate-900">No news or events right now</p>
    <p class="mt-1 text-sm text-slate-500">Check back later for company and facility updates.</p>
    <a href="{{ route('dashboard.index') }}" class="mt-6 inline-block rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-700">Return to dashboard</a>
  </div>
  @else
  <p class="mb-4 text-sm text-slate-600">{{ $newsItems->count() }} {{ $newsItems->count() === 1 ? 'item' : 'items' }} published</p>
  <div class="space-y-4">
    @foreach($newsItems as $item)
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-card">
      <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0 flex-1">
          <div class="mb-2 flex flex-wrap items-center gap-2">
            @if($item->is_global)
            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Company-wide</span>
            @else
            <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-bold uppercase text-teal-800">Facility</span>
            @endif
            @if($item->published_at)
            <span class="text-xs text-slate-500">{{ $item->published_at->format('M j, Y') }}</span>
            @endif
          </div>
          <h2 class="text-lg font-bold text-slate-950">{{ $item->title }}</h2>
          @if($item->summary)
          <p class="mt-2 text-sm text-slate-600">{{ $item->summary }}</p>
          @elseif($item->content)
          <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($item->content), 200) }}</p>
          @endif
        </div>
      </div>
    </article>
    @endforeach
  </div>
  @endif
</section>
@endsection
