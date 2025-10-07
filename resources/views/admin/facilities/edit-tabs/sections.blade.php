<div id="sections-content" class="tab-pane hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Layout & Sections</h3>
                <p class="text-sm text-gray-600">Choose which sections to display on the
                    website</p>
            </div>
            <div>
                <label for="layout_template" class="block text-sm font-bold text-gray-700 mb-1">Layout
                    Template<span class="text-red-500">*</span></label>
                <select id="layout_template" name="layout_template"
                    class="w-full rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary"
                    required>
                    @foreach($layoutTemplates as $template)
                    <option value="{{ $template }}" {{ old('layout_template', $selectedLayoutTemplate)===$template
                        ? 'selected' : '' }}>
                        {{ ucfirst($template) }}
                    </option>
                    @endforeach
                </select>
                @error('layout_template')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>



        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($availableSections as $key => $label)
            @php
            $isActive = in_array($key, $activeSections ?? []);
            $selectedVariance = $activeWebContent->variances[$key] ?? 'default';
            @endphp
            <div class="flex flex-col">
                <div class="flex items-center mb-2">
                    <input type="checkbox" id="section_{{ $key }}" name="sections[{{ $key }}]" value="1" {{ $isActive
                        ? 'checked' : '' }}
                        class="rounded border-gray-300 text-primary focus:border-primary focus:ring-primary section-toggle"
                        data-section="{{ $key }}">
                    <label for="section_{{ $key }}" class="ml-2 text-sm font-medium text-gray-700">
                        {{ $label }}
                    </label>
                </div>
                <select name="variances[{{ $key }}]" id="variance_{{ $key }}"
                    class="rounded border border-gray-400 bg-yellow-50 px-4 shadow-sm focus:border-primary focus:ring-primary"
                    {{ $isActive ? '' : 'disabled' }}>
                    @foreach($sectionVariances[$key] ?? ['default'] as $variance)
                    <option value="{{ $variance }}" {{ $selectedVariance===$variance ? 'selected' : '' }}>
                        {{ ucfirst($variance) }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endforeach
        </div>
    </div>
</div>