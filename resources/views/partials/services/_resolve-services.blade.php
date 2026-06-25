@php
use App\Models\Facility;
use App\Models\Service;
use App\Helpers\FacilityDataHelper;

$servicesCount = isset($services) && is_countable($services) ? count($services) : 0;

if ($servicesCount === 0) {
    if (isset($facility) && $facility) {
        $facilityModel = $facility instanceof Facility
            ? $facility
            : (is_array($facility) && !empty($facility['id']) ? Facility::find($facility['id']) : null);

        $services = $facilityModel
            ? FacilityDataHelper::getServices($facilityModel)->where('is_active', true)->values()
            : collect();
    } else {
        $services = Service::where('is_active', true)->orderBy('order')->get();
    }
}

@endphp
