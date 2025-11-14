<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ isset($facility) && method_exists($facility, 'getMeta') ? $facility->getMeta('title', $section) : ($title ??
    '') }}</title>
<meta name="description" content="{{ $facility->meta_description ?? $meta_description ?? '' }}">

<link rel="icon" type="image/png" href="{{ asset('bplogo.png') }}">
<link rel="apple-touch-icon" type="image/png" href="{{ asset('bplogo.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance