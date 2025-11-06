@php
use App\Models\Service;
// Color scheme variables ($primary, $secondary, $accent) are now passed from the controller.
if (isset($facility) && $facility) {
$services = $facility->services()->where('is_active', 1)->orderBy('order')->get();
} else {
$services = Service::where('is_active', 1)->orderBy('order')->get();
}
@endphp

<section id="services" class="relative overflow-hidden py-16 sm:py-24 bg-gradient-to-br from-slate-50 to-white">
  {{-- Decorative brand glows (very subtle) --}}
  <div class="pointer-events-none absolute -z-10 -top-24 -left-24 h-64 w-64 rounded-full blur-3xl opacity-15"
    style="background: {{ $primary }}"></div>
  <div class="pointer-events-none absolute -z-10 -bottom-28 -right-24 h-72 w-72 rounded-full blur-3xl opacity-10"
    style="background: {{ $accent }}"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Section header (kept) --}}
    @include('partials.section_header', [
    'section_header' => 'Our Care & Services',
    'section_sub_header' => 'Comprehensive care and enriching amenities designed to enhance quality of life for
    every resident.'
    ])

    {{-- Services Grid (refined) --}}

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach($services as $index => $service)
      <article
        class="group relative bg-white/70 backdrop-blur rounded-3xl ring-1 ring-slate-200 hover:ring-slate-300 shadow-sm hover:shadow-lg transition-all">
        {{-- Media --}}
        <div class="relative overflow-hidden rounded-t-3xl">
          <img
            src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=1000&q=80' }}"
            alt="{{ $service->name }} at {{ $facility['name'] ?? 'our facility' }}"
            class="h-44 w-full object-cover object-top sm:object-center md:object-[50%_20%] transition-transform duration-700 group-hover:scale-105"
            loading="lazy" decoding="async">
          {{-- Brand accent bar --}}
          <div class="absolute bottom-0 left-0 right-0 h-1.5" style="background: {{ $primary }}"></div>
        </div>

        {{-- Body --}}
        <div class="p-5">
          <h3 class="text-lg font-semibold text-slate-900">
            {{ $service->name }}
          </h3>
          <p class="mt-2 text-sm leading-relaxed line-clamp-3" style="color: {{ $accent }}">
            {{ $service->short_description }}
          </p>

          {{-- Actions --}}
          <div class="mt-4 flex items-center justify-center">
            <button onclick="openServiceModal('modal-{{ $index }}')"
              class="cursor-pointer inline-flex items-center gap-2 rounded-xl px-5 py-2 text-sm font-semibold text-white transition-shadow shadow hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
              style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }});"
              aria-controls="modal-{{ $index }}" aria-expanded="false">
              Details
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </article>
      @endforeach
    </div>

    {{-- CTA Panel --}}
    <div class="mt-16">
      <div class="mx-auto max-w-5xl rounded-3xl border border-slate-200 bg-white/80 backdrop-blur p-8 shadow">
        <div class="grid gap-6 md:grid-cols-[1.2fr,auto] md:items-center">
          <div>
            <h3 class="text-2xl font-bold text-slate-900">Need More Information?</h3>
            <p class="mt-2 text-slate-600">
              Our team is here to answer your questions and help you understand how our services can
              benefit you or your loved one.
            </p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3 justify-start md:justify-end">
            <a href="#contact"
              class="inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-white font-semibold shadow hover:shadow-md transition"
              style="background: {{ $primary }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" @php $primary=$primary ??
                  ($scheme->primary_color ?? '#0EA5E9');
                  $secondary = $secondary ?? ($scheme->secondary_color ?? '#1E293B');
                  $accent = $accent ?? ($scheme->accent_color ?? '#F59E0B');
                  @endphp
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0
                  005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3
                  14.284 3 6V5z" />
              </svg>
              Contact Us
            </a>
            <a href="#about"
              class="inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 font-semibold ring-2 transition bg-white text-slate-900 hover:bg-slate-50"
              style="--ring: {{ $primary }}; border-color: {{ $primary }};">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Learn About Us
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modals (accessible) --}}
  @foreach($services as $index => $service)
  <div id="modal-{{ $index }}" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog"
    aria-modal="true" aria-labelledby="modal-title-{{ $index }}">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeServiceModal('modal-{{ $index }}')">
    </div>

    {{-- Dialog --}}
    <div id="modal-panel-{{ $index }}"
      class="relative max-w-2xl w-full max-h-[90vh] overflow-y-auto transform rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 transition"
      data-motion>
      {{-- Header --}}
      <div class="flex items-center justify-between p-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
          <div
            class="w-11 h-11 rounded-xl bg-{{ $service->color ?? 'gray' }}-100 ring-1 ring-{{ $service->color ?? 'gray' }}-200 overflow-hidden">
            <img src="{{ $service->image ? asset($service->image) : '' }}" alt="" class="w-full h-full object-cover">
          </div>
          <h3 id="modal-title-{{ $index }}" class="text-xl font-bold text-slate-900">
            {{ $service->name }}
          </h3>
        </div>
        <button
          class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
          aria-label="Close" onclick="closeServiceModal('modal-{{ $index }}')">
          <svg class="h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {{-- Body --}}
      <div class="p-6">
        <div class="text-slate-700 leading-relaxed">{!! $service->detailed_description !!}</div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
          <div class="lg:col-span-1">
            <div class="h-40 rounded-xl overflow-hidden ring-1 ring-slate-200 bg-{{ $service->color ?? 'gray' }}-50">
              <img src="{{ $service->image ? asset($service->image) : '' }}" alt="{{ $service->name }}"
                class="w-full h-full object-cover">
            </div>
          </div>
          <div class="lg:col-span-2">
            <h4 class="text-base font-semibold text-slate-900 mb-3">Key Features</h4>
            @if(!empty($service->features) && is_array($service->features))
            <ul class="space-y-2">
              @foreach($service->features as $feature)
              <li class="flex items-start gap-2">
                <span
                  class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-{{ $service->color ?? 'gray' }}-100">
                  <svg class="h-3.5 w-3.5 text-{{ $service->color ?? 'gray' }}-600" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
                <span class="text-slate-700">{{ $feature }}</span>
              </li>
              @endforeach
            </ul>
            @else
            <p class="text-slate-500 italic">No features listed.</p>
            @endif
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-3">
          <a href="#contact"
            class="cursor-pointer flex-1 inline-flex items-center justify-center rounded-full px-5 py-3 font-semibold text-white transition shadow"
            style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }});"
            onclick="closeServiceModal('modal-{{ $index }}')">
            Contact Us About This Service
          </a>
          <button
            class="flex-1 inline-flex items-center justify-center rounded-full px-5 py-3 font-semibold bg-slate-100 text-slate-800 hover:bg-slate-200 transition"
            onclick="closeServiceModal('modal-{{ $index }}')">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</section>

{{-- Utilities --}}
<style>
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Reduced motion: tone down modal animation */
  @media (prefers-reduced-motion: no-preference) {
    [data-motion] {
      transform: translateY(10px) scale(.98);
      opacity: 0;
    }

    .show [data-motion] {
      transform: translateY(0) scale(1);
      opacity: 1;
      transition: transform .25s ease, opacity .25s ease;
    }
  }
</style>

<script>
  // Accessible modal helpers
  function openServiceModal(id) {
    const modal = document.getElementById(id);
    const panel = document.getElementById('modal-panel-' + id.split('-')[1]);
    if (!modal) return;

    modal.classList.add('flex','show');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Focus trap basic
    setTimeout(() => panel?.querySelector('button[aria-label="Close"]')?.focus(), 10);

    // Close on ESC
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
  }

  function closeServiceModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    document.removeEventListener('keydown', modal._esc || (()=>{}));
    modal.classList.remove('show');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
    }, 200);
  }
</script>