@php
    $active = $active ?? 'dashboard';
    $authUser = auth()->user();
    $canAccessHrPortal = $authUser?->can(\App\Support\Rbac\Permissions::ACCESS_HR_PORTAL) ?? false;
@endphp

@if($authUser)
    @php
        $extraClass = fn (bool $isActive) => $isActive
            ? 'member-portal-nav-active bg-brand-600 font-semibold shadow-lg shadow-brand-900/20 text-white'
            : 'text-teal-100';
    @endphp

    @if(\App\Support\MemberPortalLayout::userIsSystemAdmin($authUser) && ($portalNav ?? 'employee') !== 'admin')
        <div class="my-3 border-t border-white/10 pt-3">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Administration</p>
            <a href="{{ route('admin.dashboard.index') }}"
               class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs('admin.dashboard.*')) }}">
                <span>⚙️</span>
                <span>Admin Dashboard</span>
            </a>
        </div>
    @endif

    @if($authUser->hasRole(['facility-admin', 'facility-dsd']) && !in_array($portalNav ?? 'employee', ['admin', 'facility'], true))
        <div class="my-3 border-t border-white/10 pt-3">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Administration</p>
            <a href="{{ route('admin.dashboard.index') }}"
               class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs(['admin.dashboard.*', 'admin.facility.*', 'user.hr-portal', 'hr-portal.*'])) }}">
                <span>⚙️</span>
                <span>Admin Dashboard</span>
            </a>
        </div>
    @endif

    @if($canAccessHrPortal && !in_array($portalNav ?? 'employee', ['admin', 'facility'], true))
        <div class="my-3 border-t border-white/10 pt-3">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Admin</p>
            <a href="{{ route('user.hr-portal') }}"
               class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass($active === 'facility-hr-portal' || request()->routeIs('user.hr-portal')) }}">
                <span>👥</span>
                <span>HR Management</span>
            </a>
        </div>
    @endif

    @if(\App\Support\MemberPortalLayout::userCanAccessDocumentsManagement($authUser) && !in_array($portalNav ?? 'employee', ['admin', 'facility', 'corporate'], true))
        <div class="my-3 border-t border-white/10 pt-3">
            <p class="px-4 pb-2 text-xs font-semibold uppercase tracking-wide text-teal-200/80">Documents</p>
            <a href="{{ route('admin.upload-types.index') }}"
               class="member-portal-nav-link flex items-center gap-3 rounded-xl px-4 py-3 {{ $extraClass(request()->routeIs(['admin.upload-types.*', 'admin.checklist-items.*'])) }}">
                <span>📄</span>
                <span>Documents Settings</span>
            </a>
        </div>
    @endif
@endif
