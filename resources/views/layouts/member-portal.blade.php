@php
    $portalActive = $portalActive ?? 'dashboard';
    $portalTitle = $portalTitle ?? 'Bio Pacific HR Portal';
    $portalEyebrow = $portalEyebrow ?? null;
    $portalPageTitle = $portalPageTitle ?? null;
    $showPortalSearch = $showPortalSearch ?? false;
    $showPortalNotifications = $showPortalNotifications ?? true;
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
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="{{ asset('css/member-portal-sidebar.css') }}">
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
  @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 antialiased pb-20 lg:pb-0"
      x-data="{ sidebarOpen: false, profileOpen: false, notifyOpen: false }">
  <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>

  @if(session('status') && session('status') === 'profile-updated')
  <div class="fixed right-4 top-4 z-50 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg"
       x-data x-init="setTimeout(() => $el.remove(), 4000)">
    Profile saved successfully.
  </div>
  @endif

  <div class="min-h-screen lg:flex">
    <div x-show="sidebarOpen" x-transition.opacity
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
         @click="sidebarOpen = false"></div>

    @include('dashboard.member.partials.portal-sidebar', [
        'active' => $portalActive,
        'displayName' => $displayName,
        'positionTitle' => $positionTitle,
        'facilityName' => $facilityName,
        'initials' => $initials,
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

      <div class="flex-1">
        @yield('content')
      </div>

      @if($showPortalFooter)
        @include('dashboard.member.partials.portal-footer')
      @endif
    </main>
  </div>

  @hasSection('mobile-nav')
    @yield('mobile-nav')
  @else
    @include('dashboard.member.partials.portal-mobile-nav', ['active' => $portalActive])
  @endif

  @stack('scripts')
</body>
</html>
