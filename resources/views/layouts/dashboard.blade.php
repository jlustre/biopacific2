@php
    $portalActive = $portalActive ?? 'dashboard';
    $portalTitle = $portalTitle ?? 'Bio Pacific HR Management';
    $portalEyebrow = $portalEyebrow ?? 'Facility Portal';
    $portalPageTitle = $portalPageTitle ?? 'Facility Portal';
    $showPortalSearch = $showPortalSearch ?? false;
    $showPortalNotifications = $showPortalNotifications ?? false;
    $showPortalFooter = $showPortalFooter ?? false;
    $profileComplete = $profileComplete ?? 0;
    $user = $user ?? auth()->user();
    $displayName = $displayName ?? ($user->name ?? 'Employee');
    $firstNameOnly = $firstNameOnly ?? (explode(' ', trim($displayName))[0] ?? $displayName);
    $positionTitle = $positionTitle ?? 'Team Member';
    $facilityName = $facilityName ?? '—';
    $initials = $initials ?? strtoupper(substr($firstNameOnly, 0, 1));

    // Topbar H1 = sidebar purpose group (Personal, Facility, Company, …).
    if ($user) {
        $purposeMatch = \App\Support\MemberPortalLayout::activePurposeNavMatch($user);
        if ($purposeMatch) {
            $portalPageTitle = $purposeMatch['group']['label'] ?? $portalPageTitle;
            $portalEyebrow = $purposeMatch['item']['label'] ?? $portalEyebrow;
            if (! empty($purposeMatch['item']['id'])) {
                $portalActive = $purposeMatch['item']['id'];
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $portalTitle }}</title>
  <style>[x-cloak]{display:none!important}</style>
  @include('layouts.partials.facility-portal-head')
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  {{-- Alpine.js loaded via Livewire or base layout; do not include multiple times --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="{{ asset('css/member-portal-sidebar.css') }}">
  @livewireStyles
  @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 antialiased pb-20 lg:pb-0"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('memberPortalSidebarCollapsed') === 'true',
          profileOpen: false,
          notifyOpen: false,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('memberPortalSidebarCollapsed', this.sidebarCollapsed ? 'true' : 'false');
          }
      }">
  @include('layouts.partials.page-loader')
  <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>

  <div class="min-h-screen lg:flex">
    <div x-show="sidebarOpen" x-transition.opacity
         class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
         @click="sidebarOpen = false"></div>

    @include('dashboard.member.partials.portal-sidebar', [
        'active' => $portalActive,
        'profileComplete' => $profileComplete,
        'portalNav' => $portalNav ?? 'employee',
        'portalSubtitle' => $portalSubtitle ?? 'HR Employee Portal',
    ])

    <main class="flex-1 lg:min-w-0 flex flex-col min-h-screen"
          :class="{ 'member-portal-main-expanded': sidebarCollapsed }">
      @include('layouts.partials.email-verification-banner', ['user' => $user ?? auth()->user()])
      @include('dashboard.member.partials.portal-topbar', [
        'eyebrow' => $portalEyebrow,
        'pageTitle' => $portalPageTitle,
        'showSearch' => $showPortalSearch,
        'showNotifications' => $showPortalNotifications,
        'facilityName' => $facilityName,
        'firstName' => $firstNameOnly,
        'initials' => $initials,
        'portalNotifications' => $portalNotifications ?? [],
        'portalNotificationCount' => $portalNotificationCount ?? 0,
        'myTasksCount' => $myTasksCount ?? 0,
        'myMessagesCount' => $myMessagesCount ?? 0,
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

  @include('dashboard.member.partials.portal-mobile-nav', [
      'active' => $portalActive,
      'portalNav' => $portalNav ?? 'employee',
  ])

  @include('layouts.partials.go_to_top')

  @stack('scripts')
  @livewireScripts(['vite' => true])
  <script src="/js/color-scheme-dropdown.js"></script>
</body>
</html>
