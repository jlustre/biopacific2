<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Preview - {{ is_array($facility) ? $facility['name'] : $facility->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: linear-gradient(90deg, #1f2937, #374151);
            color: white;
            padding: 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .preview-content {
            margin-top: 3.5rem;
        }

        .section-label {
            position: relative;
        }

        .section-label::before {
            content: attr(data-section-name);
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(59, 130, 246, 0.9);
            color: white;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0 0 0 0.25rem;
            z-index: 10;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Preview Banner -->
    <div class="preview-banner">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-eye"></i>
                    <span class="font-semibold">Layout Preview</span>
                </div>
                <div class="text-gray-300">
                    {{ is_array($facility) ? $facility['name'] : $facility->name }} • {{ count($sections) }} sections
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.close()"
                        class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                    Close Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Content -->
    <div class="preview-content">
        @if($sections && count($sections) > 0)
            @foreach($sections as $index => $sectionData)
                <div class="section-label" data-section-name="{{ $sectionData['section']->name }} ({{ $sectionData['variant'] }})">
                    @if(view()->exists($sectionData['component_path']))
                        @include($sectionData['component_path'], [
                            'facility' => $facility,
                            'config' => $sectionData['config'],
                            'variant' => $sectionData['variant']
                        ])
                    @else
                        {{-- Fallback to default variant --}}
                        @php
                            $fallbackPath = str_replace(['.video', '.split', '.stats', '.timeline', '.cards', '.tabs', '.form', '.info', '.map'], '.default', $sectionData['component_path']);
                        @endphp

                        @if(view()->exists($fallbackPath))
                            @include($fallbackPath, [
                                'facility' => $facility,
                                'config' => $sectionData['config'],
                                'variant' => 'default'
                            ])
                        @else
                            {{-- Show section placeholder --}}
                            <div class="py-12 px-4 bg-yellow-50 border border-yellow-200 text-center">
                                <div class="max-w-md mx-auto">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-4"></i>
                                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">
                                        {{ $sectionData['section']->name }}
                                    </h3>
                                    <p class="text-sm text-yellow-700 mb-2">
                                        Component not found: {{ $sectionData['component_path'] }}
                                    </p>
                                    <p class="text-xs text-yellow-600">
                                        Variant: {{ $sectionData['variant'] }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        @else
            <div class="min-h-screen flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-layer-group text-6xl text-gray-300 mb-6"></i>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">No Sections to Preview</h2>
                    <p class="text-gray-600 mb-6">This layout doesn't have any sections configured.</p>
                    <button onclick="window.close()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Close Preview
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Section Navigation (Floating) -->
    @if($sections && count($sections) > 0)
    <div class="fixed bottom-6 right-6 z-50" x-data="{ open: false }">
        <button @click="open = !open"
                class="bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center">
            <i class="fas fa-list" x-show="!open"></i>
            <i class="fas fa-times" x-show="open"></i>
        </button>

        <div x-show="open" x-cloak
             class="absolute bottom-16 right-0 bg-white rounded-lg shadow-xl border w-64 max-h-96 overflow-y-auto">
            <div class="p-3 border-b">
                <h3 class="font-semibold text-gray-900">Sections</h3>
            </div>
            <div class="p-2">
                @foreach($sections as $index => $sectionData)
                <a href="#section-{{ $index }}"
                   class="block px-3 py-2 rounded hover:bg-gray-100 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded text-xs flex items-center justify-center">{{ $index + 1 }}</span>
                        <div>
                            <div class="font-medium">{{ $sectionData['section']->name }}</div>
                            <div class="text-gray-500 text-xs">{{ $sectionData['variant'] }}</div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <script>
        // Add section anchors for navigation
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.section-label');
            sections.forEach((section, index) => {
                section.id = `section-${index}`;
            });
        });

        // Smooth scroll for section links
        document.querySelectorAll('a[href^="#section-"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
