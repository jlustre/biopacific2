@php
    $portalActive = $portalActive ?? 'dashboard';
    $portalTitle = $portalTitle ?? 'Bio Pacific HR Management';
    $portalEyebrow = $portalEyebrow ?? null;
    $portalPageTitle = $portalPageTitle ?? null;
    $showPortalSearch = $showPortalSearch ?? false;
    $showPortalNotifications = $showPortalNotifications ?? true;
    $showPortalFooter = $showPortalFooter ?? false;
    $profileComplete = $profileComplete ?? 0;

    $user = $user ?? auth()->user();
    $displayName = $displayName ?? ($user->name ?? 'Employee');
    $firstNameOnly = $firstNameOnly ?? (explode(' ', trim($displayName))[0] ?? $displayName);
    $positionTitle = $positionTitle ?? 'Team Member';
    $facilityName = $facilityName ?? '—';
    $initials = $initials ?? strtoupper(substr($firstNameOnly, 0, 1));
    $portalUser = $user ?? auth()->user();
    if (empty($avatarUrl) && $portalUser) {
        $avatarUrl = $portalUser->profileAvatarUrl();
    }
    $primaryRoleLabel = $primaryRoleLabel ?? ($user?->primaryRoleLabel() ?? 'User');
    $userRoles = $userRoles ?? ($user?->rolesForDisplay() ?? []);
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $portalTitle }}</title>
  <style>[x-cloak]{display:none!important}</style>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  {{-- Alpine.js loaded via Livewire or base layout; do not include multiple times --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="{{ asset('css/member-portal-sidebar.css') }}">
  @livewireStyles
  @stack('head')
</head>
<body class="bg-slate-100 text-slate-800 antialiased pb-20 lg:pb-0"
      x-data="{ sidebarOpen: false, profileOpen: false, notifyOpen: false }">
  @include('layouts.partials.page-loader')
  <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>

  @if(session('status') && in_array(session('status'), ['profile-updated', 'avatar-updated'], true))
  <div class="fixed right-4 top-4 z-50 rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg"
       x-data x-init="setTimeout(() => $el.remove(), 4000)">
    {{ session('status') === 'avatar-updated' ? 'Profile photo updated.' : 'Profile saved successfully.' }}
  </div>
  @endif

  @if($user && method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail())
  @include('layouts.partials.email-verification-banner', ['user' => $user])
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
        'avatarUrl' => $avatarUrl,
        'profileComplete' => $profileComplete,
        'portalNav' => $portalNav ?? 'employee',
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
        'avatarUrl' => $avatarUrl,
        'portalNotifications' => $portalNotifications ?? [],
        'portalNotificationCount' => $portalNotificationCount ?? 0,
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
    @include('dashboard.member.partials.portal-mobile-nav', [
        'active' => $portalActive,
        'portalNav' => $portalNav ?? 'employee',
    ])
  @endif

  @include('layouts.partials.go_to_top')

  @livewireScripts(['vite' => true])
  @stack('scripts')
</body>
</html>
