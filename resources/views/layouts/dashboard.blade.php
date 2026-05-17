@php
    $useMemberPortalSidebar = $useMemberPortalSidebar ?? \App\Support\MemberPortalLayout::shouldUseForCurrentRequest();
@endphp

@if($useMemberPortalSidebar)
@php
    $portalActive = $portalActive ?? 'dashboard';
    $portalTitle = $portalTitle ?? 'Bio Pacific HR Portal';
    $portalEyebrow = $portalEyebrow ?? 'Facility Management';
    $portalPageTitle = $portalPageTitle ?? 'Facility Management';
    $showPortalSearch = $showPortalSearch ?? false;
    $showPortalNotifications = $showPortalNotifications ?? false;
    $showPortalFooter = $showPortalFooter ?? false;
    $profileComplete = $profileComplete ?? 88;
    $user = $user ?? auth()->user();
    $displayName = $displayName ?? ($user->name ?? 'Employee');
    $firstNameOnly = $firstNameOnly ?? (explode(' ', trim($displayName))[0] ?? $displayName);
    $positionTitle = $positionTitle ?? 'Team Member';
    $facilityName = $facilityName ?? '—';
    $initials = $initials ?? strtoupper(substr($firstNameOnly, 0, 1));
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $portalTitle }}</title>
  @include('layouts.partials.facility-portal-head')
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
              400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
              800: '#115e59', 900: '#134e4a'
            }
          },
          boxShadow: {
            soft: '0 18px 45px rgba(15, 23, 42, 0.08)',
            card: '0 10px 30px rgba(15, 23, 42, 0.06)'
          }
        }
      }
    }
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 antialiased pb-20 lg:pb-0"
      x-data="{ sidebarOpen: false, profileOpen: false, notifyOpen: false }">
  <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>

  <div class="min-h-screen lg:flex">
    <div x-show="sidebarOpen" x-transition.opacity
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
         @click="sidebarOpen = false"></div>

    @include('dashboard.member.partials.portal-sidebar', [
        'active' => $portalActive,
        'profileComplete' => $profileComplete,
    ])

    <main class="flex-1 lg:min-w-0 flex flex-col min-h-screen">
      @include('dashboard.member.partials.portal-topbar', [
        'eyebrow' => $portalEyebrow,
        'pageTitle' => $portalPageTitle,
        'showSearch' => $showPortalSearch,
        'showNotifications' => $showPortalNotifications,
        'facilityName' => $facilityName,
        'firstName' => $firstNameOnly,
        'initials' => $initials,
      ])

      <div class="flex-1 min-w-0">
        @hasSection('header')
        <div class="border-b border-slate-200 bg-white/80">
          <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @yield('header')
          </div>
        </div>
        @endif
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          @yield('content')
        </div>
      </div>
    </main>
  </div>

  @include('dashboard.member.partials.portal-mobile-nav', ['active' => $portalActive])

  @stack('scripts')
  @livewireScripts(['vite' => true])
  <script src="/js/color-scheme-dropdown.js"></script>
</body>
</html>
@else
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title')@else Bio-Pacific Healthcare - Admin Dashboard @endif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/color-scheme-dropdown.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    @livewireStyles
    <link rel="icon" href="{{ asset('images/bplogo.png') }}" type="image/png">

</head>

<body class="min-h-screen antialiased"
    style="background-image: url('{{ asset('images/auth_background.jpg') }}'); background-size: cover; background-position: center;">
    @include('layouts.partials.go_to_top')

    @include('layouts.topnav')
    <div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
        @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
        x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 })">

        @include('layouts.sidebar')

        <div class="flex-1 -pl-2 sm:p-3 md:p-4 lg:p-6">
            <div :class="sidebarOpen ? 'pt-20 pl-64' : 'pt-20 pl-4'">
                @hasSection('header')
                <div class="bg-white/60 shadow-sm border-b border-gray-200 rounded-xl">
                    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
                        @yield('header')
                    </div>
                </div>
                @endif

                <main class="bg-teal-50/60 rounded-lg max-w-full mx-auto px-2 sm:p-3 md:p-4 lg:p-4">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @stack('scripts')
    @livewireScripts(['vite' => true])
    <script>
        if (typeof Alpine === 'undefined') {
            const alpineScript = document.createElement('script');
            alpineScript.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
            alpineScript.defer = true;
            document.head.appendChild(alpineScript);
        }
    </script>
    <script src="/js/color-scheme-dropdown.js"></script>
</body>

</html>
@endif
