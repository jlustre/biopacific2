<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Title --}}
  <title>{{ $metaTitle ?? ($title ?? 'Bio Pacific Facilities') }}</title>

  {{-- Basic SEO --}}
  <meta name="description" content="{{ $metaDescription ?? 'Compassionate senior care across California.' }}">
  @isset($metaKeywords)
    <meta name="keywords" content="{{ $metaKeywords }}">
  @endisset
  <meta name="robots" content="{{ $robots ?? 'index,follow' }}">

  {{-- Canonical --}}
  @isset($canonical)
    <link rel="canonical" href="{{ $canonical }}">
  @endisset

  {{-- Open Graph / Facebook --}}
  <meta property="og:type" content="website">
  <meta property="og:title" content="{{ $metaTitle ?? ($title ?? 'Bio Pacific Nursing Home Sites') }}">
  <meta property="og:description" content="{{ $metaDescription ?? 'Compassionate senior care across California.' }}">
  @isset($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
  @endisset
  @isset($canonical)
    <meta property="og:url" content="{{ $canonical }}">
  @endisset

  {{-- Twitter Card --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $metaTitle ?? ($title ?? 'Bio Pacific Nursing Home Sites') }}">
  <meta name="twitter:description" content="{{ $metaDescription ?? 'Compassionate senior care across California.' }}">
  @isset($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
  @endisset

  {{-- Per-page extra head content (e.g., JSON-LD) --}}
  @stack('meta')

  @vite(['resources/css/app.css','resources/js/app.js'])
  @livewireStyles
</head>
<body class="antialiased bg-gray-50 text-gray-900">
  {{ $slot }}
  @livewireScripts
</body>
</html>

