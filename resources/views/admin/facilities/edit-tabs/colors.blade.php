<div id="colors-content" class="tab-pane hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Brand Colors</h3>
        <div class="mb-6">
            <label for="color_scheme_id" class="block text-sm font-medium text-gray-700 mb-2">Color
                Scheme</label>
            <select id="color_scheme_id" name="color_scheme_id"
                class="w-full md:w-1/2 lg:w-1/3 rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary">
                @foreach($colorSchemes as $scheme)
                <option value="{{ $scheme->id }}" data-primary="{{ $scheme->primary_color }}"
                    data-secondary="{{ $scheme->secondary_color }}" data-accent="{{ $scheme->accent_color }}" {{
                    ($facility->color_scheme_id ??
                    1) == $scheme->id ? 'selected' : '' }}>
                    {{ $scheme->id }}: {{ $scheme->name }}
                </option>
                @endforeach
            </select>
            <script>
                function showSchemePreview() {
                                            var select = document.getElementById('color_scheme_id');
                                            var selectedId = select.value;
                                            document.querySelectorAll('.color-scheme-preview').forEach(function(div) {
                                                div.style.display = div.getAttribute('data-scheme-id') === selectedId ? 'flex' : 'none';
                                            });
                                        }
                                        document.getElementById('color_scheme_id').addEventListener('change', function() {
                                            updateColorPreview();
                                            showSchemePreview();
                                        });
                                        document.addEventListener('DOMContentLoaded', function() {
                                            setTimeout(function() {
                                                updateColorPreview();
                                                showSchemePreview();
                                            }, 50);
                                        });
            </script>
            </select>

            <div id="color-preview" class="mt-6 flex gap-6">
                <div class="flex flex-col items-center">
                    <span class="block w-10 h-10 rounded-full border" id="preview-primary"></span>
                    <span class="text-xs mt-2">Primary</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="block w-10 h-10 rounded-full border" id="preview-secondary"></span>
                    <span class="text-xs mt-2">Secondary</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="block w-10 h-10 rounded-full border" id="preview-accent"></span>
                    <span class="text-xs mt-2">Accent</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="block w-10 h-10 rounded-full border" id="preview-neutral-dark"></span>
                    <span class="text-xs mt-2">Neutral Dark</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="block w-10 h-10 rounded-full border" id="preview-neutral-light"></span>
                    <span class="text-xs mt-2">Neutral Light</span>
                </div>
            </div>
            <script>
                function updateColorPreview() {
                    var select = document.getElementById('color_scheme_id');
                    if (!select) return;
                    var selected = select.options[select.selectedIndex];
                    document.getElementById('preview-primary').style.backgroundColor = selected.getAttribute('data-primary') || '#007bff';
                    document.getElementById('preview-secondary').style.backgroundColor = selected.getAttribute('data-secondary') || '#6c757d';
                    document.getElementById('preview-accent').style.backgroundColor = selected.getAttribute('data-accent') || '#28a745';
                    document.getElementById('preview-neutral-dark').style.backgroundColor = selected.getAttribute('data-neutral-dark') || '#343a40';
                    document.getElementById('preview-neutral-light').style.backgroundColor = selected.getAttribute('data-neutral-light') || '#f8f9fa';
                }
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof initColorSchemeDropdown === 'function') {
                        initColorSchemeDropdown();
                    }
                    setTimeout(updateColorPreview, 50);
                    // Tab persistence
                    var lastTab = localStorage.getItem('facilityEditActiveTab') || 'basic';
                    showTab(lastTab);
                    document.querySelectorAll('.tab-button').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            localStorage.setItem('facilityEditActiveTab', this.id.replace('-tab',''));
                        });
                    });
                    document.getElementById('color_scheme_id').addEventListener('change', updateColorPreview);
                });
            </script>
        </div>
    </div>
</div>