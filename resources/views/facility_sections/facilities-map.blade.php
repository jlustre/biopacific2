{{-- Facilities Map Section --}}
{{-- Debug output removed --}}
<h2 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-4 text-center" style="color: {{ $primary }}">Our Locations</h2>
<div id="facilities-map" style="height: 500px; width: 100%;"></div>

<script>
    const facilities = @json($facilities);
    function initMap() {
        const center = facilities.length ? {
            lat: Number(facilities[0].latitude),
            lng: Number(facilities[0].longitude)
        } : { lat: 34.052235, lng: -118.243683 };
        const GOOGLE_MAP_ID = @json(config('services.google_map_id'));
        const map = new google.maps.Map(document.getElementById('facilities-map'), {
            zoom: 7,
            center: center,
            mapId: GOOGLE_MAP_ID
        });
        facilities.forEach(facility => {
            const lat = Number(facility.latitude);
            const lng = Number(facility.longitude);
            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = new google.maps.marker.AdvancedMarkerElement({
                    position: { lat: lat, lng: lng },
                    map: map,
                    title: facility.name,
                });
                // Optionally, add a label or custom content
                // marker.content = document.createElement('div');
                // marker.content.textContent = facility.name;
            }
        });
    }

    const GOOGLE_MAPS_API_KEY = @json(config('services.google_maps_api_key'));
    function loadGoogleMapsScript() {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&callback=initMap&libraries=marker`;
        script.async = true;
        script.defer = true;
        document.body.appendChild(script);
    }

    window.addEventListener('DOMContentLoaded', loadGoogleMapsScript);
</script>