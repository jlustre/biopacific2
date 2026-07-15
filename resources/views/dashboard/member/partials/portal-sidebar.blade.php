@php
    $active = $active ?? 'dashboard';
    $profileComplete = $profileComplete ?? 0;
    $portalNav = $portalNav ?? 'employee';
    $currentUser = auth()->user();
    $purposeGroups = \App\Support\MemberPortalLayout::purposeGroupsForUser($currentUser);
    $portalSubtitle = $portalSubtitle ?? match ($portalNav) {
        'admin' => 'Admin Portal',
        'corporate' => 'Corporate Management',
        'facility' => 'Facility Portal',
        default => 'HR Employee Portal',
    };
    $corporatePublicUrl = route('facility.public', ['facility' => \App\Models\Facility::corporateSiteSlug()]);
@endphp

<aside :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        sidebarCollapsed ? 'lg:-ml-72 lg:-translate-x-full' : 'lg:ml-0 lg:translate-x-0'
    ]"
    class="member-portal-sidebar fixed inset-y-0 left-0 z-40 w-80 shrink-0 transform bg-teal-800 text-white transition-[transform,margin] duration-300 ease-in-out lg:static lg:w-72">
  <div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
      <a href="{{ $corporatePublicUrl }}"
        class="flex min-w-0 flex-1 items-center gap-3 rounded-xl transition hover:bg-white/10"
        title="Visit Bio-Pacific corporate website">
        <div class="member-portal-sidebar-brand flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white p-1.5 shadow-lg">
          <img src="{{ asset('images/bplogo.png') }}" alt="Bio-Pacific" class="h-full w-full object-contain">
        </div>
        <div class="min-w-0">
          <p class="text-lg font-bold leading-tight">Bio-Pacific</p>
          <p class="text-xs text-teal-100">{{ $portalSubtitle }}</p>
        </div>
      </a>
      <button type="button" class="rounded-lg p-2 text-teal-100 hover:bg-white/10 lg:hidden"
        @click="sidebarOpen = false" aria-label="Close menu">✕</button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-4 pt-4 pb-6 text-sm">
      @include('dashboard.member.partials.portal-sidebar-purpose-groups', [
          'active' => $active,
          'groups' => $purposeGroups,
      ])
    </nav>

    <div class="border-t border-white/10 p-4">
      @if($active === 'profile' || request()->routeIs(['settings.profile', 'settings.profile.*']))
      <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
        <p class="text-sm font-semibold">Profile Record Status</p>
        <div class="mt-3 h-2 rounded-full bg-white/20">
          <div class="h-2 rounded-full bg-teal-300" style="width: {{ min(100, max(0, $profileComplete)) }}%"></div>
        </div>
        <p class="mt-2 text-xs text-teal-100">{{ $profileComplete }}% complete</p>
      </div>
      @endif
    </div>
  </div>
</aside>
