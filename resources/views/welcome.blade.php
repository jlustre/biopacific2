@extends('layouts.layout1')

@section('content')
<!-- Hero -->

@include('partials.hero')

<!-- About -->
@include('partials.about')

<!-- Services & Amenities -->
@include('partials.services')

<!-- Rooms & Rates -->
@include('partials.rooms')

<!-- Careers -->
@include('partials.careers')

<!-- News & Events -->
@include('partials.news')

<!-- Testimonials -->
@include('partials.testimonials')

<!-- Gallery -->
@include('partials.gallery')

<!-- Contact -->
@include('partials.contact')

<!-- FAQs -->
@include('partials.faq')

<!-- Resources -->
@include('partials.resources')

<!-- Book a Tour CTA -->
<section id="book" class="py-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="rounded-3xl bg-gradient-to-r from-primary/10 to-accent/10 p-8 sm:p-12 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
      <div>
        <h3 class="text-2xl font-bold text-secondary">Ready to see Vale Healthcare Center?</h3>
        <p class="text-slate-700 mt-1">Schedule a guided tour and meet our team.</p>
      </div>
      <a href="#contact" class="inline-flex items-center rounded-xl bg-primary px-6 py-3 text-white font-medium hover:bg-primary/90">Book a Tour</a>
    </div>
  </div>
</section>
@endsection
