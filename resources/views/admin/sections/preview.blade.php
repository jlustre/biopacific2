<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview: {{ $section->name }}{{ $variant !== 'default' ? ' (' . ucfirst($variant) . ')' : '' }}</title>

    {{-- Dynamic theme colors --}}
    <style>
        :root {
            --color-primary: {{ $mockFacility['primary_color'] ?? '#047857' }};
            --color-secondary: {{ $mockFacility['secondary_color'] ?? '#1f2937' }};
            --color-accent: {{ $mockFacility['accent_color'] ?? '#06b6d4' }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Preview-specific styles */
        .preview-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .preview-controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .preview-info {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            max-width: 300px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Preview Badge -->
    <div class="preview-badge">
        <i class="fas fa-eye mr-2"></i>
        Section Preview: {{ $section->name }}{{ $variant !== 'default' ? ' (' . ucfirst($variant) . ')' : '' }}
    </div>

    <!-- Preview Info -->
    <div class="preview-info">
        <h3 class="font-semibold text-gray-900 mb-2">Section Information</h3>
        <div class="text-sm text-gray-600 space-y-1">
            <div><strong>Name:</strong> {{ $section->name }}</div>
            <div><strong>Slug:</strong> {{ $section->slug }}</div>
            <div><strong>Variant:</strong> {{ ucfirst($variant) }}</div>
            <div><strong>Component:</strong> <code class="text-xs">{{ $section->component_path }}.{{ $variant }}</code></div>
        </div>
    </div>

    <!-- Preview Controls -->
    <div class="preview-controls">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sections.show', $section->id) }}"
               class="text-sm text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Section
            </a>

            @if($section->variants && count($section->variants) > 1)
                <div class="relative">
                    <select onchange="window.location.href = this.value"
                            class="text-sm border border-gray-300 rounded px-2 py-1">
                        @foreach($section->variants as $variantOption)
                            @php
                                $variantName = is_array($variantOption) ? ($variantOption['name'] ?? 'default') : (is_object($variantOption) ? ($variantOption->name ?? 'default') : $variantOption);
                            @endphp
                            <option value="{{ route('admin.sections.preview', [$section->id, $variantName]) }}"
                                    {{ $variantName === $variant ? 'selected' : '' }}>
                                {{ ucfirst($variantName) }} Variant
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <a href="{{ route('admin.sections.edit', $section->id) }}"
               class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fas fa-edit mr-1"></i>
                Edit Section
            </a>
        </div>
    </div>

    <!-- Section Preview Container -->
    <div class="min-h-screen bg-white">
        @php
            // Build the component path for the variant
            $componentPath = $section->component_path . '.' . $variant;

            // Fallback paths if the variant doesn't exist
            $fallbackPaths = [
                $section->component_path . '.default',
                $section->component_path,
            ];

            $viewPaths = array_merge([$componentPath], $fallbackPaths);
            $rendered = false;
        @endphp

        @foreach($viewPaths as $viewPath)
            @if(!$rendered && View::exists($viewPath))
                @include($viewPath, [
                    'facility' => $mockFacility,
                    'config' => [],
                    'variant' => $variant
                ])
                @php $rendered = true; @endphp
                @break
            @endif
        @endforeach

        @if(!$rendered)
            <!-- Fallback content for missing section view -->
            <div class="min-h-screen flex items-center justify-center bg-gray-50">
                <div class="text-center max-w-md mx-auto px-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 mb-2">View Not Found</h2>
                    <p class="text-gray-600 mb-6">
                        The Blade template for this section variant could not be found.
                    </p>

                    <div class="bg-gray-100 rounded-lg p-4 mb-6">
                        <h3 class="font-medium text-gray-900 mb-2">Expected file paths:</h3>
                        <div class="text-sm text-gray-600 space-y-1 text-left">
                            @foreach($viewPaths as $path)
                                <div class="font-mono bg-white px-3 py-1 rounded border">
                                    resources/views/{{ str_replace('.', '/', $path) }}.blade.php
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('admin.sections.edit', $section->id) }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Edit Section
                        </a>
                        <a href="{{ route('admin.sections.show', $section->id) }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                            Back to Details
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Development Helper (only show in local environment) -->
    @if(app()->environment('local'))
        <div style="position: fixed; top: 20px; left: 20px; background: rgba(0, 0, 0, 0.8); color: white; padding: 8px 12px; border-radius: 6px; font-size: 11px; font-family: monospace; z-index: 1001;">
            <div>Component: {{ $componentPath }}</div>
            <div>Rendered: {{ $rendered ? 'Yes' : 'No' }}</div>
            @if($rendered)
                <div style="color: #10b981;">✓ Template found</div>
            @else
                <div style="color: #ef4444;">✗ Template missing</div>
            @endif
        </div>
    @endif
</body>
</html>
