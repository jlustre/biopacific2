@php
    $eyebrow = $eyebrow ?? null;
    $pageTitle = $pageTitle ?? null;
    $showSearch = $showSearch ?? false;
    $showNotifications = $showNotifications ?? true;
    $facilityName = $facilityName ?? '—';
    $firstName = $firstName ?? 'Employee';
    $initials = $initials ?? 'E';
    $avatarUrl = $avatarUrl ?? null;
    $portalNotifications = $portalNotifications ?? [];
    $portalNotificationCount = (int) ($portalNotificationCount ?? count($portalNotifications));
    $myTasksCount = (int) ($myTasksCount ?? 0);
    $myMessagesCount = (int) ($myMessagesCount ?? 0);
    $notificationToneClass = fn (string $tone) => match ($tone) {
        'rose' => 'bg-rose-50',
        'amber' => 'bg-amber-50',
        'brand' => 'bg-brand-50',
        default => 'bg-slate-50',
    };
    $notificationTitleClass = fn (string $tone) => match ($tone) {
        'rose' => 'text-rose-800',
        'amber' => 'text-amber-800',
        'brand' => 'text-brand-800',
        default => 'text-slate-800',
    };
    $notificationMessageClass = fn (string $tone) => match ($tone) {
        'rose' => 'text-rose-600',
        'amber' => 'text-amber-600',
        'brand' => 'text-brand-600',
        default => 'text-slate-600',
    };
@endphp

<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
  <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 min-w-0">
      <button type="button"
        class="rounded-xl border border-slate-200 bg-white p-2 text-slate-700 shadow-sm lg:hidden shrink-0"
        @click="sidebarOpen = true" aria-label="Open menu">☰</button>
      @if($eyebrow || $pageTitle)
      <div class="min-w-0">
        @if($eyebrow)
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500 truncate">{{ $eyebrow }}</p>
        @endif
        @if($pageTitle)
        <h1 class="text-xl font-bold text-slate-950 sm:text-2xl truncate">{{ $pageTitle }}</h1>
        @endif
      </div>
      @endif
    </div>

    @if($showSearch)
    <div class="hidden flex-1 items-center justify-center px-6 xl:flex">
      <div class="relative w-full max-w-xl">
        <span class="absolute left-4 top-2.5 text-slate-400">⌕</span>
        <input type="search" placeholder="Search policies, documents, benefits, schedules..."
          class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm outline-none ring-brand-500/20 focus:border-brand-400 focus:ring-4" />
      </div>
    </div>
    @endif

    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
      @hasSection('header-actions')
        @yield('header-actions')
      @endif

      <span class="hidden max-w-[200px] truncate text-sm font-bold text-slate-900 md:block" title="{{ $facilityName }}">
        {{ $facilityName }}
      </span>

      @if(\Illuminate\Support\Facades\Route::has('member.messages'))
      <a href="{{ route('member.messages') }}"
         class="relative rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm hover:bg-slate-50 {{ request()->routeIs(['member.messages', 'member.messages.*']) ? 'ring-2 ring-teal-500/30 border-teal-300' : '' }}"
         aria-label="My Messages{{ $myMessagesCount > 0 ? ' ('.$myMessagesCount.')' : '' }}"
         title="My Messages">
        <i class="fa-solid fa-comments text-slate-700"></i>
        @if($myMessagesCount > 0)
        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
          {{ $myMessagesCount > 99 ? '99+' : $myMessagesCount }}
        </span>
        @endif
      </a>
      @endif

      @if(\Illuminate\Support\Facades\Route::has('member.tasks'))
      <a href="{{ route('member.tasks') }}"
         class="relative rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm hover:bg-slate-50 {{ request()->routeIs(['member.tasks', 'member.tasks.*']) ? 'ring-2 ring-teal-500/30 border-teal-300' : '' }}"
         aria-label="My Tasks{{ $myTasksCount > 0 ? ' ('.$myTasksCount.')' : '' }}"
         title="My Tasks">
        <i class="fa-solid fa-list-check text-slate-700"></i>
        @if($myTasksCount > 0)
        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-teal-600 px-1 text-[10px] font-bold text-white">
          {{ $myTasksCount > 99 ? '99+' : $myTasksCount }}
        </span>
        @endif
      </a>
      @endif

      @if($showNotifications)
      <div class="relative">
        <button type="button" @click="notifyOpen = !notifyOpen; profileOpen = false"
          class="relative rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm hover:bg-slate-50"
          aria-label="Notifications">
          🔔
          @if($portalNotificationCount > 0)
          <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
            {{ $portalNotificationCount > 9 ? '9+' : $portalNotificationCount }}
          </span>
          @endif
        </button>
        <div x-show="notifyOpen" x-cloak x-transition @click.outside="notifyOpen = false"
          class="absolute right-0 mt-3 w-80 rounded-3xl border border-slate-200 bg-white p-4 shadow-soft z-50">
          <div class="mb-3 flex items-center justify-between">
            <p class="font-bold text-slate-950">Notifications</p>
            <a href="{{ route('member.messages') }}" class="text-xs font-semibold text-brand-600">View all</a>
          </div>
          @if(count($portalNotifications) === 0)
          <p class="rounded-2xl bg-slate-50 p-3 text-sm text-slate-600">You’re caught up. New reminders will appear here when something needs your attention.</p>
          @else
          <div class="space-y-3 text-sm">
            @foreach($portalNotifications as $notification)
            @php
                $tone = $notification['tone'] ?? 'slate';
            @endphp
            @if(!empty($notification['route']))
            <a href="{{ $notification['route'] }}" class="block rounded-2xl p-3 {{ $notificationToneClass($tone) }} hover:opacity-90">
              <p class="font-semibold {{ $notificationTitleClass($tone) }}">{{ $notification['title'] }}</p>
              <p class="text-xs {{ $notificationMessageClass($tone) }}">{{ $notification['message'] }}</p>
            </a>
            @else
            <div class="rounded-2xl p-3 {{ $notificationToneClass($tone) }}">
              <p class="font-semibold {{ $notificationTitleClass($tone) }}">{{ $notification['title'] }}</p>
              <p class="text-xs {{ $notificationMessageClass($tone) }}">{{ $notification['message'] }}</p>
            </div>
            @endif
            @endforeach
          </div>
          @endif
        </div>
      </div>
      @endif

      <div class="relative">
        <button type="button" @click="profileOpen = !profileOpen; notifyOpen = false"
          class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-1.5 pr-3 shadow-sm hover:bg-slate-50">
          @include('dashboard.member.partials.user-avatar', [
              'avatarUrl' => $avatarUrl,
              'initials' => $initials,
              'size' => 'sm',
              'shape' => 'rounded-full',
          ])
          <span class="hidden text-sm font-semibold sm:inline">{{ $firstName }}</span>
        </button>
        <div x-show="profileOpen" x-cloak x-transition @click.outside="profileOpen = false"
          class="absolute right-0 mt-3 w-56 rounded-3xl border border-slate-200 bg-white p-3 shadow-soft z-50">
          <div class="mb-2 rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600">
            <span class="font-bold text-slate-800">Role</span>
            <p class="mt-0.5 font-semibold text-slate-900">{{ $primaryRoleLabel ?? 'User' }}</p>
          </div>
          <a href="{{ route('settings.profile') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-100">My Profile</a>
          <a href="{{ route('settings.password') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-100">Settings</a>
          <a href="#" class="block rounded-xl px-3 py-2 text-sm hover:bg-slate-100">Switch Facility</a>
          <button type="button" onclick="document.getElementById('logout-form').submit()"
            class="block w-full rounded-xl px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Sign Out</button>
        </div>
      </div>
    </div>
  </div>
</header>
