@props(['facilities', 'type', 'selected' => null])
<div class="mb-2 bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-1">Select a facility for {{ ucfirst($type) }}</h2>
    <div class="flex items-center gap-4">
        <select name="facility" id="facility-select"
            class="form-select block w-full max-w-xs rounded-sm border-2 border-teal-700 focus:border-primary focus:ring focus:ring-primary/50 px-2 py-1">
            <option value="">-- Choose Facility --</option>
            @foreach($facilities as $facility)
            <option value="{{ $facility->id }}" {{ $selected==$facility->id ? 'selected' : '' }}>
                {{ $facility->name }} ({{ $facility->city ?? 'N/A' }}, {{ $facility->state ?? 'N/A' }})
            </option>
            @endforeach
        </select>
        <button type="button" id="facility-go-btn"
            class="ml-2 px-4 py-2 bg-white text-gray-50 rounded-lg hover:bg-gray-100 focus:outline-none">Go</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('facility-select');
            const goBtn = document.getElementById('facility-go-btn');
            function redirectToFacility() {
                const facilityId = select.value;
                let route = '';
                if (facilityId) {
                    switch ('{{ $type }}') {
                        case 'gallery':
                            route = '/admin/facilities/' + facilityId + '/galleries';
                            break;
                        case 'news':
                            route = '/admin/news?facility=' + facilityId;
                            break;
                        case 'testimonial':
                            route = '/admin/testimonials?facility=' + facilityId;
                            break;
                        case 'faq':
                            route = '/admin/faqs?facility=' + facilityId;
                            break;
                        default:
                            alert('Unknown type selected. Please contact support.');
                            return;
                    }
                    window.location.href = route;
                }
            }
            goBtn.addEventListener('click', redirectToFacility);
            select.addEventListener('change', function() {
                if (select.value) {
                    redirectToFacility();
                }
            });
        });
    </script>
</div>