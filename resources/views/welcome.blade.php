@extends('layouts.dynamic')

@section('content')
{{-- Dynamic Layout Sections --}}
<x-dynamic-layout />

{{-- Book a Tour CTA - This could also be made into a dynamic section --}}
<section id="book" class="py-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-r from-primary/10 to-accent/10 p-8 sm:p-12 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6" style="background: linear-gradient(to right, {{ $facility['primary_color'] ?? '#047857' }}10, {{ $facility['accent_color'] ?? '#06b6d4' }}10);">
      <div>
        <h3 class="text-2xl font-bold text-secondary">Ready to see <span class="text-primary">{{ $facility['name'] }}</span>?</h3>
        <p class="text-slate-700 mt-1">Schedule a guided tour and meet our team.</p>
      </div>
      <a href="#contact" class="inline-flex items-center rounded-xl bg-primary px-6 py-3 text-white font-medium hover:bg-primary/90" style="background-color: {{ $facility['primary_color'] ?? '#047857' }};">Book a Tour</a>
    </div>
  </div>
</section>
@endsection
