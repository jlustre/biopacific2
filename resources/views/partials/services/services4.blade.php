@php
use App\Models\Service;
if (isset($facility) && $facility) {
$services = $facility->services()->where('is_active', 1)->orderBy('order')->get();
} else {
$services = Service::where('is_active', 1)->orderBy('order')->get();
}
@endphp

<section id="services" class="py-16 bg-gradient-to-br from-teal-800 via-teal-400 to-teal-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-extrabold text-white mb-3 tracking-tight drop-shadow">Our Signature Services</h2>
            <p class="text-lg text-teal-100 max-w-2xl mx-auto">Premium care, innovative amenities, and a vibrant
                community—see what makes us unique.</p>
        </div>
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($services as $service)
            <div
                class="relative bg-white/90 rounded-2xl shadow-2xl hover:shadow-blue-400/40 transition group overflow-hidden flex flex-col h-full">
                <div class="relative h-44 overflow-hidden">
                    <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                        alt="{{ $service->name }}"
                        class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-700">
                    <span
                        class="absolute bottom-3 left-3 text-white text-lg font-bold px-4 py-1 rounded-full shadow-lg uppercase tracking-wide"
                        style="background: {{ $accent }}">{{
                        $service->name }}</span>
                </div>
                <div class="flex-1 flex flex-col p-6">
                    <h3 class="text-xl font-bold mb-2" style="color: {{ $primary }}">{{ $service->name }}</h3>
                    <p class="text-blue-800 mb-4 text-base line-clamp-3">{{ $service->short_description }}</p>
                    <ul class="mb-4 space-y-1 text-blue-900 text-sm">
                        @foreach($service->features as $feature)
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-600" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto pt-4">
                        <button onclick="openServiceModal4('modal-{{ $service->id }}')"
                            class="w-full inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-teal-700 to-teal-500 text-white font-semibold rounded-full shadow hover:from-teal-800 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-400 transition cursor-pointer">Details</button>
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
        <div class="relative max-w-2xl w-full bg-white rounded-3xl shadow-2xl ring-1 ring-blue-900/10 p-10 mx-auto flex flex-col items-center"
            style="max-height:90vh; overflow-y:auto;">
            <button
                class="absolute top-4 right-4 text-teal-700 hover:text-teal-900 focus:outline-none text-2xl font-bold"
                aria-label="Close" onclick="closeServiceModal4('modal-{{ $service->id }}')">&times;</button>
            <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=600&q=80' }}"
                alt="{{ $service->name }}" class="w-32 h-32 object-cover rounded-xl mb-6 shadow">
            <h3 id="modal-title-{{ $service->id }}" class="text-3xl font-bold mb-2" style="color: {{ $accent }}">{{
                $service->name }}
            </h3>
            <div class="mb-6 text-left text-lg w-full">{!! $service->detailed_description !!}</div>
            <div class="w-full mb-6">
                <h4 class="text-base font-semibold text-teal-900 mb-2">Key Features</h4>
                <ul class="list-disc list-inside space-y-1 text-teal-900">
                    @foreach($service->features as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="#contact"
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-teal-700 to-teal-500 text-white font-semibold rounded-full shadow hover:from-teal-800 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-blue-400 transition mt-2"
                onclick="closeServiceModal4('modal-{{ $service->id }}')">Contact Us About This Service</a>
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
    function openServiceModal4(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('button[aria-label="Close"]').focus(), 10);
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal4(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
}
function closeServiceModal4(id) {
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