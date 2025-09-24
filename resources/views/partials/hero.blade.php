<section class="relative overflow-hidden">
  <div class="absolute inset-0">
    <img src="{{ asset('images/hero1.jpg') }}" alt="Warm nursing home common area with residents and staff"
      class="h-full w-full object-cover opacity-70">
  </div>
  <div class="relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
      <div class="max-w-xl bg-white/60 backdrop-blur rounded-2xl p-8 shadow-xl">
        <h1 class="text-3xl sm:text-5xl font-extrabold text-primary">{{ $facility['hero_main_heading'] }}</h1>
        <p class="mt-4 text-slate-700">{{ $facility['hero_sub_heading'] }}</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <a href="#contact"
            class="inline-flex items-center rounded-xl bg-primary px-5 py-3 text-white font-medium hover:bg-primary/90">Quick
            Contact</a>
          <a href="#book"
            class="inline-flex items-center rounded-xl border border-primary text-primary px-5 py-3 font-medium hover:bg-primary/10">Book
            a Tour</a>
        </div>
        <dl class="mt-8 grid grid-cols-2 gap-4 text-sm">
          <div class="bg-white/70 rounded-xl p-4">
            <dt class="font-semibold">Beds Available</dt>
            <dd class="mt-1">Limited — call to confirm</dd>
          </div>
          <div class="bg-white/70 rounded-xl p-4">
            <dt class="font-semibold">Specialty Services</dt>
            <dd class="mt-1">Rehab, Memory Care, Hospice</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</section>