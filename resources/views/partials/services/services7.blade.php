@php
use App\Models\Service;
if (isset($facility) && $facility) {
$services = $facility->services()->where('is_active', 1)->orderBy('order')->get();
} else {
$services = Service::where('is_active', 1)->orderBy('order')->get();
}
@endphp

<section id="services" class="py-16 bg-gradient-to-tr from-sky-50 via-gray-50 to-blue-100">
    {{-- Subtle texture / brand glow --}}
    <div
        class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,0.7),transparent_60%)]">
    </div>
    {{-- Color variables ($primary, $secondary, $accent) are now passed from the controller. --}}
    <div class="pointer-events-none absolute -top-24 -left-24 h-80 w-80 rounded-full blur-3xl opacity-20"
        style="background: {{ $primary }}"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full blur-3xl opacity-15"
        style="background: {{ $accent }}"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-extrabold text-blue-900 mb-3 tracking-tight drop-shadow">Our Services</h2>
            <p class="text-lg text-blue-700 max-w-2xl mx-auto">Swipe through our services—each card highlights a unique
                offering for residents.</p>
        </div>
        <div class="relative">
            <button id="slider-left"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-blue-700 text-white font-bold rounded-full shadow-lg w-12 h-12 flex items-center justify-center hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                style="box-shadow: 0 2px 8px #38bdf8;" aria-label="Scroll Left">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button id="slider-right"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-blue-700 text-white font-bold rounded-full shadow-lg w-12 h-12 flex items-center justify-center hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                style="box-shadow: 0 2px 8px #38bdf8;" aria-label="Scroll Right">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div id="service-slider" class="flex gap-8 overflow-x-auto pb-4 snap-x snap-mandatory scroll-smooth">
                @foreach($services as $service)
                <div
                    class="min-w-[320px] max-w-xs flex-shrink-0 bg-white rounded-2xl shadow-lg hover:shadow-blue-300 transition group overflow-hidden flex flex-col h-full border-2 border-blue-100 relative snap-center">
                    <div
                        class="relative h-64 overflow-hidden flex items-center justify-center bg-gradient-to-tr from-blue-100 to-sky-100">
                        <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                            alt="{{ $service->name }}"
                            class="w-56 h-56 object-cover object-center rounded-2xl shadow-lg group-hover:scale-105 transition duration-700">
                        <span
                            class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-blue-700 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg uppercase tracking-wide text-center w-fit">{{
                            $service->name }}</span>
                    </div>
                    <div class="flex-1 flex flex-col p-6">
                        <h3 class="text-xl font-bold text-blue-900 mb-2">{{ $service->name }}</h3>
                        <p class="text-blue-700 mb-4 text-base line-clamp-3">{{ $service->short_description }}</p>
                        <ul class="mb-4 space-y-1 text-blue-900 text-sm">
                            @foreach($service->features as $feature)
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-sky-400" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-auto pt-4">
                            <button onclick="openServiceModal7('modal-{{ $service->id }}')"
                                class="cursor-pointer w-full inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-blue-500 to-sky-500 text-white font-semibold rounded-full shadow hover:from-blue-600 hover:to-sky-600 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">Learn
                                More</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach($services as $service)
    <div id="modal-{{ $service->id }}"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog"
        aria-modal="true" aria-labelledby="modal-title-{{ $service->id }}">
        <div class="relative max-w-xl w-full bg-white rounded-3xl shadow-2xl ring-1 ring-blue-900/10 p-8 mx-auto flex flex-col items-center"
            style="max-height:90vh; overflow-y:auto;">
            <button
                class="absolute top-4 right-4 text-blue-700 hover:text-blue-900 focus:outline-none text-2xl font-bold"
                aria-label="Close" onclick="closeServiceModal7('modal-{{ $service->id }}')">&times;</button>
            <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                alt="{{ $service->name }}" class="w-40 h-40 object-cover rounded-2xl mb-4 shadow">
            <h3 id="modal-title-{{ $service->id }}" class="text-2xl font-bold text-blue-900 mb-2">{{ $service->name }}
            </h3>
            <div class="text-blue-700 mb-4 text-left text-base w-full">{!! $service->detailed_description !!}</div>
            <div class="w-full mb-4">
                <h4 class="text-base font-semibold text-blue-900 mb-2">Key Features</h4>
                <ul class="list-disc list-inside space-y-1 text-blue-900">
                    @foreach($service->features as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="#contact"
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-500 to-sky-500 text-white font-semibold rounded-full shadow hover:from-blue-600 hover:to-sky-600 focus:outline-none focus:ring-2 focus:ring-blue-400 transition mt-2"
                onclick="closeServiceModal7('modal-{{ $service->id }}')">Contact Us About This Service</a>
        </div>
    </div>
    @endforeach
</section>

<style>
    #line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #service-slider {
        scrollbar-width: thin;
        scrollbar-color: #38bdf8 #e0e7ef;
    }

    #service-slider::-webkit-scrollbar {
        height: 8px;
    }

    #service-slider::-webkit-scrollbar-thumb {
        background: #38bdf8;
        border-radius: 4px;
    }

    #service-slider::-webkit-scrollbar-track {
        background: #e0e7ef;
    }
</style>

<script>
    function openServiceModal7(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('button[aria-label="Close"]').focus(), 10);
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal7(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
}
function closeServiceModal7(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    document.removeEventListener('keydown', modal._esc || (()=>{}));
    modal.classList.remove('flex');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}
// Slider arrow scroll logic
const slider = document.getElementById('service-slider');
const leftBtn = document.getElementById('slider-left');
const rightBtn = document.getElementById('slider-right');
if (slider && leftBtn && rightBtn) {
    leftBtn.onclick = function() {
        slider.scrollBy({ left: -350, behavior: 'smooth' });
    };
    rightBtn.onclick = function() {
        slider.scrollBy({ left: 350, behavior: 'smooth' });
    };
}
</script>