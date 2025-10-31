@php
use App\Models\Service;
if (isset($facility) && $facility) {
$services = $facility->services()->where('is_active', 1)->orderBy('order')->get();
} else {
$services = Service::where('is_active', 1)->orderBy('order')->get();
}
@endphp

<section id="services" class="py-16"
    style="background: linear-gradient(to top right, {{ $primary }}, {{ $accent }}, {{ $accent }});">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold mb-3 tracking-tight drop-shadow" style="color: {{ $primary }}">Discover
                Our Service
                Spectrum</h2>
            <p class="text-lg max-w-2xl mx-auto" style="color: {{ $neutral_dark }}">A curated blend of care, comfort,
                and
                innovation—crafted for every resident's journey.</p>
        </div>
        <div class="grid gap-10 md:grid-cols-3 xl:grid-cols-5">
            @foreach($services as $service)
            <div class="relative bg-white rounded-full shadow-xl transition group overflow-hidden flex flex-col items-center p-8 border-2 h-full"
                style="border: 1px solid {{ $secondary }}; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);">
                <div class="w-24 h-24 mb-4 rounded-full overflow-hidden flex items-center justify-center"
                    style="border: 4px solid {{ $secondary }}">
                    <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=200&q=80' }}"
                        alt="{{ $service->name }}" class="h-auto w-full max-w-full object-cover object-center"
                        style="border: 3px solid {{ $secondary }}">
                </div>
                <h3 class="text-lg font-bold mb-2 text-center" style="color: {{ $secondary }};">{{ $service->name }}
                </h3>
                <p class="mb-4 text-sm text-center line-clamp-3" style="color: {{ $neutral_dark }}">{{
                    $service->short_description }}</p>
                <ul class="mb-4 space-y-1 text-xs text-left w-full" style="color: {{ $neutral_dark }}">
                    @foreach($service->features as $feature)
                    <li class="flex items-center gap-2"><svg class="w-3 h-3" style="color: {{ $secondary }}" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>{{ $feature }}</li>
                    @endforeach
                </ul>
                <button onclick="openServiceModal6('modal-{{ $service->id }}')"
                    class="cursor-pointer mt-auto px-5 py-2.5"
                    style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }}); color: white; font-weight: 600; border-radius: 9999px; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); transition: background 0.3s, transform 0.3s;"
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                    More Info</button>
            </div>
            @endforeach
        </div>
    </div>

    @foreach($services as $service)
    <div id="modal-{{ $service->id }}"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm" role="dialog"
        aria-modal="true" aria-labelledby="modal-title-{{ $service->id }}">
        <div class="relative max-w-lg w-full bg-white rounded-3xl shadow-2xl ring-1 p-8 mx-auto flex flex-col items-center"
            style="max-height:90vh; overflow-y:auto;">
            <button class="absolute top-4 right-4 focus:outline-none text-2xl font-bold"
                style="color: {{ $primary }}; transition: color 0.3s;" aria-label="Close"
                onclick="closeServiceModal6('modal-{{ $service->id }}')">&times;</button>
            <img src="{{ $service->image ? asset($service->image) : 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=200&q=80' }}"
                alt="{{ $service->name }}" class="w-20 h-20 object-cover rounded-full mb-4 shadow">
            <h3 id="modal-title-{{ $service->id }}" class="text-xl font-bold mb-2 text-center"
                style="color: {{ $primary }}">{{
                $service->name }}</h3>
            <div class="mb-4 text-left text-base w-full" style="color: {{ $neutral_dark }}">{!!
                $service->detailed_description !!}</div>
            <div class="w-full mb-4">
                <h4 class="text-base font-semibold mb-2" style="color: {{ $secondary }}">Key Features</h4>
                <ul class="list-disc list-inside space-y-1" style="color: {{ $neutral_dark }}">
                    @foreach($service->features as $feature)
                    <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="#contact"
                class="cursor-pointer inline-flex items-center px-8 py-3 font-semibold rounded-full shadow focus:outline-none focus:ring-2 transition mt-2"
                style="background: linear-gradient(to right, {{ $primary }}, {{ $accent }}); font-weight: 600; border-radius: 9999px; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); transition: background 0.3s, transform 0.3s; color: white;"
                onclick="closeServiceModal6('modal-{{ $service->id }}')">Contact Us About This Service</a>
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
    function openServiceModal6(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => modal.querySelector('button[aria-label="Close"]').focus(), 10);
    function onEsc(e){ if (e.key === 'Escape') closeServiceModal6(id); }
    modal._esc = onEsc;
    document.addEventListener('keydown', onEsc);
}
function closeServiceModal6(id) {
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