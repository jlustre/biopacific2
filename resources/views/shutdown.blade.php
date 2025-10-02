`
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex flex-col items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg w-full text-center">
            @if(!($isGlobal ?? false) && isset($facilityName))
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-800">{{ $facilityName }}</h2>
            </div>
            @endif
            <img src="{{ asset('images/website_maintenance.png') }}" alt="Maintenance" class="mx-auto mb-6 w-32 h-32">
            <h1 class="text-2xl font-bold mb-4">
                {{ $isGlobal ? 'All Facilities Under Maintenance' : 'Facility Under Maintenance' }}
            </h1>
            <p class="mb-4 text-gray-700">
                {{ $message ?? 'This facility website is temporarily unavailable due to scheduled maintenance.' }}
            </p>
            @if($eta)
            <!-- DEBUG: Raw ETA value: {{ $eta }} -->
            <p class="mb-2 text-gray-600">Estimated time back online:</p>
            <p id="countdown" class="font-semibold text-lg text-blue-600"></p>
            <script>
                function startCountdown(etaString) {
                    const countdownEl = document.getElementById('countdown');
                    // Defensive: remove any whitespace or line breaks, parse as local time
                    let etaIso = (etaString || '').replace(/\s+/g, '');
                    const eta = new Date(etaIso);
                    if (isNaN(eta.getTime())) {
                        countdownEl.textContent = 'Invalid ETA (' + etaString + ')';
                        return;
                    }
                    function updateCountdown() {
                        const now = new Date();
                        let diff = eta - now;
                        if (diff <= 0) {
                            countdownEl.textContent = 'Now online!';
                            return;
                        }
                        const hours = Math.floor(diff / (1000 * 60 * 60));
                        diff -= hours * 1000 * 60 * 60;
                        const minutes = Math.floor(diff / (1000 * 60));
                        diff -= minutes * 1000 * 60;
                        const seconds = Math.floor(diff / 1000);
                        countdownEl.textContent = `${hours}h ${minutes}m ${seconds}s remaining`;
                        setTimeout(updateCountdown, 1000);
                    }
                    updateCountdown();
                }
                document.addEventListener('DOMContentLoaded', function() {
                    var etaIso = '';
                    try {
                        etaIso = @json(\Carbon\Carbon::parse($eta)->toIso8601ZuluString());
                    } catch (e) {
                        document.getElementById('countdown').textContent = 'Invalid ETA (parse error)';
                        return;
                    }
                    etaIso = (etaIso || '').replace(/\s+/g, '');
                    startCountdown(etaIso);
                });
            </script>
            @else
            <p class="mb-2 text-gray-600">No estimated re-enable time set.</p>
            @endif
            <p class="mt-6 text-sm text-gray-400">We apologize for the inconvenience and appreciate your patience.</p>
        </div>
    </div>
</body>

</html>