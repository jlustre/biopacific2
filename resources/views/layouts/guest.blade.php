<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($facility) && method_exists($facility, 'getMeta') ? $facility->getMeta('title', $section) :
        (config('app.name', 'Facility')) }}</title>
    @php
    $currentSection = $section ?? 'home';
    $metaDescription = '';
    if (isset($facility['meta_description']) && is_array($facility['meta_description']) &&
    isset($facility['meta_description'][$currentSection]['meta_description']) &&
    is_string($facility['meta_description'][$currentSection]['meta_description'])) {
    $metaDescription = $facility['meta_description'][$currentSection]['meta_description'];
    } elseif (isset($facility['meta_description']) && is_string($facility['meta_description'])) {
    $metaDescription = $facility['meta_description'];
    } else {
    $metaDescription = config('app.description', '');
    }
    @endphp
    <meta name="description" content="{{ $metaDescription }}">

<body class="font-sans text-gray-900 antialiased"
    style="background: url('@secureAsset('images/auth_background.jpg')') center center / cover no-repeat;">
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 bg-opacity-80">
        <div>
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-teal-500" />
            </a>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>