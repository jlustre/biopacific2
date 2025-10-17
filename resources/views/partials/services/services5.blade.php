@php
use App\Models\Service;
if (isset($facility) && $facility) {
$services = $facility->services()->where('is_active', 1)->orderBy('order')->get();
} else {
$services = Service::where('is_active', 1)->orderBy('order')->get();
}
@endphp

<section id="services" class="py-16 bg-gradient-to-bl from-green-50 via-yellow-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-extrabold text-green-900 mb-3 tracking-tight drop-shadow">Our Innovative Services
            </h2>
            <p class="text-lg text-green-700 max-w-2xl mx-auto">A fresh approach to care, amenities, and
                wellness—tailored for every resident.</p>
        </div>
        <div class="flex flex-wrap gap-8 justify-center">
            @foreach($services as $service)
            <div
                class="w-full sm:w-2/3 md:w-1/2 lg:w-1/3 xl:w-1/4 bg-white rounded-xl shadow-lg hover:shadow-green-300 transition group overflow-hidden flex flex-col h-full border-2 border-green-100 relative">
                <div
                    class="relative h-48 overflow-hidden flex items-center justify-center bg-gradient-to-tr from-green-100 to-blue-100">
                    <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                        alt="{{ $service->name }}"
                        class="w-36 h-36 object-cover object-center rounded-lg shadow-lg group-hover:scale-105 transition duration-700">
                    <span
                        class="absolute top-3 left-3 bg-green-700 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg uppercase tracking-wide">{{
                        $service->name }}</span>
                </div>
                <div class="flex-1 flex flex-col p-6">
                    <h3 class="text-xl font-bold text-green-900 mb-2">{{ $service->name }}</h3>
                    <p class="text-green-700 mb-4 text-base line-clamp-3">{{ $service->short_description }}</p>
                    <ul class="mb-4 space-y-1 text-green-900 text-sm">
                        @foreach($service->features as $feature)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-yellow-400" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto pt-4">
                        <button onclick="openServiceModal5('modal-{{ $service->id }}')"
                            class="w-full inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold rounded-full shadow hover:from-green-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-green-400 transition cursor-pointer">Service
                            Details</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @foreach($services as $service)
    <div id="modal-{{ $service->id }}"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog"
        aria-modal="true" aria-labelledby="modal-title-{{ $service->id }}">
        <div class="relative max-w-xl w-full bg-white rounded-3xl shadow-2xl ring-1 ring-green-900/10 p-8 mx-auto flex flex-col items-center"
            style="max-height:90vh; overflow-y:auto;">
            <button
                class="absolute top-4 right-4 text-green-700 hover:text-green-900 focus:outline-none text-2xl font-bold"
                aria-label="Close" onclick="closeServiceModal5('modal-{{ $service->id }}')">&times;</button>
            <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                alt="{{ $service->name }}" class="w-24 h-24 object-cover rounded-xl mb-4 shadow">
            <h3 id="modal-title-{{ $service->id }}" class="text-2xl font-bold text-green-900 mb-2">{{ $service->name }}
            </h3>
            <div class="text-green-700 mb-4 text-left text-base w-full">{!! $service->detailed_description !!}</div>
            <div class="w-full mb-4">
                <h4 class="text-base font-semibold text-green-900 mb-2">Key Features</h4>
                <ul class="list-disc list-inside space-y-1 text-green-900">
                    @foreach($service->features as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="#contact"
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold rounded-full shadow hover:from-green-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-green-400 transition mt-2"
                onclick="closeServiceModal5('modal-{{ $service->id }}')">Contact Us About This Service</a>
        </div>
    </div>
    @endforeach
</section>

<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    function openServiceModal5(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('button[aria-label="Close"]').focus(), 10);
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal5(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
}
function closeServiceModal5(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    document.removeEventListener('keydown', modal._esc || (()=>{}));
    modal.classList.remove('flex');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}
</script>