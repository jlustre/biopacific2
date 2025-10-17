@php
use App\Models\Service;
if (isset($facility) && $facility) {
$services = $facility->services()->where('is_active', 1)->orderBy('order')->get();
} else {
$services = Service::where('is_active', 1)->orderBy('order')->get();
}
@endphp

<section id="services" class="py-16 bg-gradient-to-tr from-pink-100 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-extrabold text-indigo-900 mb-3 tracking-tight drop-shadow">Our Distinctive Services
            </h2>
            <p class="text-lg text-indigo-700 max-w-2xl mx-auto">Experience a new level of care and amenities, designed
                for comfort, wellness, and vibrant living.</p>
        </div>
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($services as $service)
            <div
                class="relative bg-white rounded-2xl shadow-lg hover:shadow-indigo-300 transition group overflow-hidden flex flex-col h-full border border-indigo-100">
                <div
                    class="relative h-40 overflow-hidden flex items-center justify-center bg-gradient-to-br from-indigo-100 to-pink-100">
                    <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                        alt="{{ $service->name }}"
                        class="w-32 h-32 object-cover object-center rounded-xl shadow-lg group-hover:scale-105 transition duration-700">
                </div>
                <div class="flex-1 flex flex-col p-6">
                    <h3 class="text-xl font-bold text-indigo-900 mb-2">{{ $service->name }}</h3>
                    <p class="text-indigo-700 mb-4 text-base line-clamp-3">{{ $service->short_description }}</p>
                    <ul class="mb-4 space-y-1 text-indigo-900 text-sm">
                        @foreach($service->features as $feature)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-pink-400" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto pt-4">
                        <button onclick="openServiceModal3('modal-{{ $service->id }}')"
                            class="w-full inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-pink-500 to-indigo-500 text-white font-semibold rounded-full shadow hover:from-pink-600 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition cursor-pointer">View
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
        <div class="relative max-w-xl w-full bg-white rounded-3xl shadow-2xl ring-1 ring-indigo-900/10 p-8 mx-auto flex flex-col items-center"
            style="max-height:90vh; overflow-y:auto;">
            <button
                class="absolute top-4 right-4 text-indigo-700 hover:text-indigo-900 focus:outline-none text-2xl font-bold"
                aria-label="Close" onclick="closeServiceModal3('modal-{{ $service->id }}')">&times;</button>
            <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                alt="{{ $service->name }}" class="w-24 h-24 object-cover rounded-xl mb-4 shadow">
            <h3 id="modal-title-{{ $service->id }}" class="text-2xl font-bold text-indigo-900 mb-2">{{ $service->name }}
            </h3>
            <div class="text-indigo-700 mb-4 text-left text-base w-full">{!! $service->detailed_description !!}</div>
            <div class="w-full mb-4">
                <h4 class="text-base font-semibold text-indigo-900 mb-2">Key Features</h4>
                <ul class="list-disc list-inside space-y-1 text-indigo-900">
                    @foreach($service->features as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="#contact"
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-pink-500 to-indigo-500 text-white font-semibold rounded-full shadow hover:from-pink-600 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition mt-2"
                onclick="closeServiceModal3('modal-{{ $service->id }}')">Contact Us About This Service</a>
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
    function openServiceModal3(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('button[aria-label="Close"]').focus(), 10);
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal3(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
}
function closeServiceModal3(id) {
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