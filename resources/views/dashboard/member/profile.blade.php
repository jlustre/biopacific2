@extends('layouts.member-portal')

@section('content')
<div x-data="{
  activeTab: 'overview',
  editMode: false,
  init() {
    const hash = window.location.hash.replace('#', '');
    const tabs = ['overview','personal','employment','certifications','documents','competencies','history','security'];
    if (tabs.includes(hash)) { this.activeTab = hash; }
  },
  toggleEdit() {
    if (this.editMode) {
      this.$refs.profileForm?.requestSubmit();
    } else {
      this.editMode = true;
      this.activeTab = 'personal';
    }
  }
}">
<section class="px-4 py-6 pb-24 sm:px-6 lg:px-8">
  <div class="mb-4 flex justify-end">
    <button type="button" @click="toggleEdit()" class="rounded-2xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700" x-text="editMode ? 'Save Changes' : 'Edit Profile'"></button>
  </div>

  <div class="relative overflow-hidden rounded-[2rem] bg-teal-700 text-white shadow-soft">
    <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-teal-600/40" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-12 h-48 w-48 rounded-full bg-teal-800/50" aria-hidden="true"></div>
    <div class="relative z-10 p-6 sm:p-8">
      <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
          <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-3xl bg-white/20 text-3xl font-black ring-4 ring-white/30 sm:h-28 sm:w-28 sm:text-4xl">{{ $initials }}</div>
          <div>
            <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">Employee Record</div>
            <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">{{ $displayName }}</h2>
            <p class="mt-2 text-lg text-teal-50">{{ $positionTitle }} · {{ $departmentName }}</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
              <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">Employee ID: {{ $employeeId }}</span>
              <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">{{ $facilityName }}</span>
              @if($hireDate !== '—')
              <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">Hired {{ $hireDate }}</span>
              @endif
              <span class="rounded-full bg-white/15 px-3 py-1 ring-1 ring-white/20">{{ $primaryRoleLabel }}</span>
              <span class="rounded-full bg-emerald-400/20 px-3 py-1 text-emerald-100 ring-1 ring-emerald-300/30">Active</span>
            </div>
          </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-3 xl:w-[520px]">
          <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/20">
            <p class="text-xs text-teal-100">Profile Complete</p>
            <p class="mt-1 text-2xl font-black">{{ $profileComplete }}%</p>
            <div class="mt-2 h-2 rounded-full bg-white/20"><div class="h-2 rounded-full bg-teal-300" style="width: {{ $profileComplete }}%"></div></div>
          </div>
          <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/20">
            <p class="text-xs text-teal-100">Facility</p>
            <p class="mt-1 text-lg font-black leading-tight">{{ $facilityName }}</p>
          </div>
          <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/20">
            <p class="text-xs text-teal-100">Account Email</p>
            <p class="mt-1 text-sm font-black leading-tight">{{ $user->email }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6 grid gap-6 xl:grid-cols-12">
    <aside class="xl:col-span-3">
      <div class="sticky top-24 rounded-[2rem] border border-slate-200 bg-white p-4 shadow-card">
        <p class="px-3 pb-3 text-xs font-bold uppercase tracking-wide text-slate-500">Profile Sections</p>
        <nav class="space-y-1 text-sm font-semibold">
          @foreach([
            ['overview', 'Overview', null],
            ['personal', 'Personal Information', null],
            ['employment', 'Employment Record', null],
            ['certifications', 'Licenses & Certifications', '2'],
            ['documents', 'Documents', null],
            ['competencies', 'Competency Review', null],
            ['history', 'History & Audit Log', null],
            ['security', 'Security Settings', null],
          ] as [$tab, $label, $badge])
          <button type="button" @click="activeTab='{{ $tab }}'" :class="activeTab==='{{ $tab }}' ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left">
            <span>{{ $label }}</span>
            @if($badge)<span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs text-rose-700">{{ $badge }}</span>@else<span>›</span>@endif
          </button>
          @endforeach
        </nav>
      </div>
    </aside>

    <div class="space-y-6 xl:col-span-6">
      @include('dashboard.member.partials.profile-tab-content')
    </div>

    <aside class="space-y-6 xl:col-span-3">
      <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="text-lg font-black text-slate-950">Record Actions</h3>
        <div class="mt-5 grid gap-3 text-sm font-bold">
          <a href="{{ route('employment.portal') }}" class="rounded-2xl bg-teal-600 px-4 py-3 text-center text-white hover:bg-teal-700">Employment Portal</a>
          <a href="{{ route('pre-employment.portal') }}" class="rounded-2xl border border-teal-200 px-4 py-3 text-center text-teal-700 hover:bg-teal-50">Pre-Employment</a>
          <a href="{{ route('member.news-events.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-center text-slate-700 hover:bg-slate-50">News & Events</a>
          <a href="{{ route('settings.password') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-center text-slate-700 hover:bg-slate-50">Change Password</a>
        </div>
      </section>
      <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="text-lg font-black text-slate-950">Account</h3>
        <div class="mt-5 space-y-3 text-sm">
          <div class="rounded-2xl bg-teal-50 p-4">
            <p class="text-xs font-bold uppercase text-teal-700">Role</p>
            <p class="mt-1 font-black text-slate-950">{{ $primaryRoleLabel }}</p>
            @if(!empty($userRoles))
            <div class="mt-2 flex flex-wrap gap-1.5">
              @foreach($userRoles as $role)
              <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-bold text-teal-800 ring-1 ring-teal-200">{{ $role['label'] }}</span>
              @endforeach
            </div>
            @endif
          </div>
          <div class="rounded-2xl bg-teal-50 p-4">
            <p class="text-xs font-bold uppercase text-teal-700">Login Email</p>
            <p class="mt-1 font-black text-slate-950">{{ $user->email }}</p>
          </div>
          <p class="text-xs text-slate-500">Payroll and tax fields are managed by HR in your official employee record.</p>
        </div>
      </section>
    </aside>
  </div>
</section>
</div>
@endsection

@section('mobile-nav')
@include('dashboard.member.partials.portal-profile-mobile-nav')
@endsection
