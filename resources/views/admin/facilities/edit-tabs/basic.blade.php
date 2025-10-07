<div id="basic-content" class="tab-pane">
    <div class="p-6">
        <!-- Facility Shutdown Controls -->
        <div class="mb-8 p-4 bg-yellow-100 border-l-4 border-yellow-400 rounded">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
                Facility Shutdown (Maintenance Mode)
            </h3>
            <div class="flex items-center mb-2">
                <input type="checkbox" id="is_shutdown" name="is_shutdown" value="1" {{ old('is_shutdown',
                    $facility->is_shutdown) ? 'checked' : '' }}
                class="rounded border-gray-300 text-yellow-600 focus:border-yellow-600
                focus:ring-yellow-600">
                <label for="is_shutdown" class="ml-2 text-sm font-medium text-yellow-900">Temporarily disable this
                    facility website</label>
            </div>
            <div class="mb-2" id="shutdown-message-group">
                <label for="shutdown_message" class="block text-sm font-medium text-yellow-900 mb-1">Shutdown
                    Message</label>
                <input type="text" id="shutdown_message" name="shutdown_message"
                    value="{{ old('shutdown_message', ($facility->shutdown_message ?? '') !== '' ? $facility->shutdown_message : 'This facility is temporarily unavailable due to maintenance.') }}"
                    class="w-full rounded border border-yellow-300 bg-yellow-50 px-4 shadow-sm focus:border-yellow-600 focus:ring-yellow-600">
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                                                var shutdownCheckbox = document.getElementById('is_shutdown');
                                                var messageInput = document.getElementById('shutdown_message');
                                                var defaultMsg = 'This facility is temporarily unavailable due to maintenance.';
                                                if (shutdownCheckbox && messageInput) {
                                                    shutdownCheckbox.addEventListener('change', function() {
                                                        if (this.checked && !messageInput.value) {
                                                            messageInput.value = defaultMsg;
                                                        }
                                                    });
                                                }
                                            });
                </script>
                <small class="text-yellow-700">This message will be shown to users during
                    shutdown.</small>
            </div>
            <div id="shutdown-eta-group">
                <label for="shutdown_eta" class="block text-sm font-medium text-yellow-900 mb-1">Estimated Re-Enable
                    Time</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mt-2 w-full">
                    <div class="flex items-center gap-1 w-full sm:w-auto">
                        <input type="number" min="1" step="1" id="shutdown_eta_hours" placeholder="Hours"
                            class="w-20 rounded border border-yellow-300 bg-yellow-50 px-2 py-1 shadow-sm focus:border-yellow-600 focus:ring-yellow-600" />
                        <span class="text-sm text-yellow-700">hours</span>
                    </div>
                    <input type="datetime-local" id="shutdown_eta" name="shutdown_eta"
                        value="{{ old('shutdown_eta', $facility->shutdown_eta ? \Carbon\Carbon::parse($facility->shutdown_eta)->format('Y-m-d\\TH:i') : '' ) }}"
                        class="flex-1 min-w-0 rounded border border-yellow-300 bg-yellow-50 px-4 shadow-sm focus:border-yellow-600 focus:ring-yellow-600" />
                    <span id="eta-countdown" class="text-blue-700 font-semibold whitespace-nowrap"></span>
                </div>
                <small class="text-yellow-700">Let users know when the site is expected to be
                    available again. You can enter a date/time directly or specify hours
                    above.</small>
            </div>
            <script>
                function updateEtaCountdown() {
                                            var etaInput = document.getElementById('shutdown_eta');
                                            var countdownEl = document.getElementById('eta-countdown');
                                            if (!etaInput || !countdownEl) return;
                                            var etaVal = etaInput.value;
                                            if (!etaVal) {
                                                countdownEl.textContent = '';
                                                return;
                                            }
                                            var eta = new Date(etaVal);
                                            if (isNaN(eta.getTime())) {
                                                countdownEl.textContent = 'Invalid ETA';
                                                return;
                                            }
                                            function tick() {
                                                var now = new Date();
                                                var diff = eta - now;
                                                if (diff <= 0) {
                                                    countdownEl.textContent = 'Now online!';
                                                    return;
                                                }
                                                var hours = Math.floor(diff / (1000 * 60 * 60));
                                                diff -= hours * 1000 * 60 * 60;
                                                var minutes = Math.floor(diff / (1000 * 60));
                                                diff -= minutes * 1000 * 60;
                                                var seconds = Math.floor(diff / 1000);
                                                countdownEl.textContent = `${hours}h ${minutes}m ${seconds}s remaining`;
                                                countdownEl._timeout = setTimeout(tick, 1000);
                                            }
                                            if (countdownEl._timeout) clearTimeout(countdownEl._timeout);
                                            tick();
                                        }
                                        document.addEventListener('DOMContentLoaded', function() {
                                            var hoursInput = document.getElementById('shutdown_eta_hours');
                                            var etaInput = document.getElementById('shutdown_eta');
                                            var shutdownCheckbox = document.getElementById('is_shutdown');
                                            var etaGroup = document.getElementById('shutdown-eta-group');
                                            var messageGroup = document.getElementById('shutdown-message-group');
                                            var messageInput = document.getElementById('shutdown_message');
                                            function toggleShutdownFields() {
                                                if (shutdownCheckbox.checked) {
                                                    etaGroup.style.display = '';
                                                    messageGroup.style.display = '';
                                                    if (etaInput) etaInput.disabled = false;
                                                    if (messageInput) messageInput.disabled = false;
                                                } else {
                                                    etaGroup.style.display = 'none';
                                                    messageGroup.style.display = 'none';
                                                    if (etaInput) {
                                                        etaInput.value = '';
                                                        etaInput.disabled = true;
                                                    }
                                                    if (hoursInput) {
                                                        hoursInput.value = '';
                                                    }
                                                    if (messageInput) {
                                                        messageInput.value = '';
                                                        messageInput.disabled = true;
                                                    }
                                                    var countdownEl = document.getElementById('eta-countdown');
                                                    if (countdownEl) countdownEl.textContent = '';
                                                }
                                            }
                                            if (shutdownCheckbox && etaGroup && messageGroup) {
                                                shutdownCheckbox.addEventListener('change', toggleShutdownFields);
                                                toggleShutdownFields();
                                            }
                                            if (hoursInput && etaInput) {
                                                hoursInput.addEventListener('input', function() {
                                                    var hours = parseInt(this.value, 10);
                                                    if (!isNaN(hours) && hours > 0) {
                                                        var now = new Date();
                                                        now.setHours(now.getHours() + hours);
                                                        var year = now.getFullYear();
                                                        var month = String(now.getMonth() + 1).padStart(2, '0');
                                                        var day = String(now.getDate()).padStart(2, '0');
                                                        var hour = String(now.getHours()).padStart(2, '0');
                                                        var minute = String(now.getMinutes()).padStart(2, '0');
                                                        var localDatetime = `${year}-${month}-${day}T${hour}:${minute}`;
                                                        etaInput.value = localDatetime;
                                                        updateEtaCountdown();
                                                    }
                                                });
                                            }
                                            if (etaInput) {
                                                etaInput.addEventListener('input', updateEtaCountdown);
                                                updateEtaCountdown();
                                            }
                                            // On form submit, if shutdown is unchecked, clear eta and message fields
                                            var form = etaInput && etaInput.form;
                                            if (form) {
                                                form.addEventListener('submit', function() {
                                                    if (shutdownCheckbox && !shutdownCheckbox.checked) {
                                                        if (etaInput) etaInput.value = '';
                                                        if (messageInput) messageInput.value = '';
                                                    }
                                                });
                                            }
                                        });
            </script>
            <script>
                function updateEtaCountdown() {
                                                var etaInput = document.getElementById('shutdown_eta');
                                                var countdownEl = document.getElementById('eta-countdown');
                                                if (!etaInput || !countdownEl) return;
                                                var etaVal = etaInput.value;
                                                if (!etaVal) {
                                                    countdownEl.textContent = '';
                                                    return;
                                                }
                                                var eta = new Date(etaVal);
                                                if (isNaN(eta.getTime())) {
                                                    countdownEl.textContent = 'Invalid ETA';
                                                    return;
                                                }
                                                function tick() {
                                                    var now = new Date();
                                                    var diff = eta - now;
                                                    if (diff <= 0) {
                                                        countdownEl.textContent = 'Now online!';
                                                        return;
                                                    }
                                                    var hours = Math.floor(diff / (1000 * 60 * 60));
                                                    diff -= hours * 1000 * 60 * 60;
                                                    var minutes = Math.floor(diff / (1000 * 60));
                                                    diff -= minutes * 1000 * 60;
                                                    var seconds = Math.floor(diff / 1000);
                                                    countdownEl.textContent = `${hours}h ${minutes}m ${seconds}s remaining`;
                                                    countdownEl._timeout = setTimeout(tick, 1000);
                                                }
                                                if (countdownEl._timeout) clearTimeout(countdownEl._timeout);
                                                tick();
                                            }
                                            document.addEventListener('DOMContentLoaded', function() {
                                                var hoursInput = document.getElementById('shutdown_eta_hours');
                                                var etaInput = document.getElementById('shutdown_eta');
                                                if (hoursInput && etaInput) {
                                                    hoursInput.addEventListener('input', function() {
                                                        var hours = parseInt(this.value, 10);
                                                        if (!isNaN(hours) && hours > 0) {
                                                            var now = new Date();
                                                            now.setHours(now.getHours() + hours);
                                                            var year = now.getFullYear();
                                                            var month = String(now.getMonth() + 1).padStart(2, '0');
                                                            var day = String(now.getDate()).padStart(2, '0');
                                                            var hour = String(now.getHours()).padStart(2, '0');
                                                            var minute = String(now.getMinutes()).padStart(2, '0');
                                                            var localDatetime = `${year}-${month}-${day}T${hour}:${minute}`;
                                                            etaInput.value = localDatetime;
                                                            updateEtaCountdown();
                                                        }
                                                    });
                                                }
                                                if (etaInput) {
                                                    etaInput.addEventListener('input', updateEtaCountdown);
                                                    updateEtaCountdown();
                                                }
                                            });
            </script>
        </div>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Basic Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Facility
                Name<span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $facility->name) }}"
                class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary"
                required>
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tagline" class="block text-sm font-bold text-gray-700 mb-2">Tagline</label>
            <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $facility->tagline) }}"
                class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
            @error('tagline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="domain" class="block text-sm font-bold text-gray-700 mb-2">Domain</label>
            <input type="text" id="domain" name="domain" value="{{ old('domain', $facility->domain) }}"
                placeholder="example.com"
                class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
            @error('domain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subdomain" class="block text-sm font-bold text-gray-700 mb-2">Subdomain</label>
            <input type="text" id="subdomain" name="subdomain" value="{{ old('subdomain', $facility->subdomain) }}"
                placeholder="facility-name"
                class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
            @error('subdomain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active',
                    $facility->is_active) ? 'checked' : '' }}
                class="rounded border-gray-300 text-primary focus:border-primary
                focus:ring-primary">
                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Facility
                    is
                    active</label>
            </div>
            <div class="mb-4">
                <label for="location_map" class="block text-sm font-bold text-gray-700 mb-2">Location
                    Map</label>
                @if($facility->location_map)
                @if(Str::startsWith($facility->location_map, ['http://', 'https://']))
                <div class="mb-2">
                    <iframe src="{{ $facility->location_map }}" width="100%" height="200" style="border:0;"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                @else
                <div class="mb-2">
                    {!! $facility->location_map !!}
                </div>
                @endif
                @endif
                <input type="text" id="location_map" name="location_map"
                    value="{{ old('location_map', $facility->location_map) }}"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary"
                    placeholder="Paste Google Maps embed code or URL here">
                @error('location_map')<p class="mt-1 text-sm text-red-600">{{ $message
                    }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>