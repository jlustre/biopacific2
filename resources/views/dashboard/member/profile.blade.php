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
  <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
    <a href="{{ route('dashboard.index') }}" class="rounded-2xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-bold text-brand-700 shadow-sm hover:bg-brand-50">Dashboard</a>
    <button type="button" @click="toggleEdit()" class="rounded-2xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700" x-text="editMode ? 'Save Changes' : 'Edit Profile'"></button>
  </div>
  <div class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-800 via-brand-700 to-brand-900 text-white shadow-soft">
    <div class="relative p-6 sm:p-8">
      <div class="absolute right-0 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
      <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
          <div class="relative h-28 w-28 shrink-0">
            <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-white/20 text-4xl font-black ring-4 ring-white/30">{{ $initials }}</div>
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-3xl font-black tracking-tight sm:text-4xl">{{ $displayName }}</h2>
              <span class="rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-bold text-emerald-100 ring-1 ring-emerald-300/30">Active</span>
            </div>
            <p class="mt-2 text-lg text-brand-50">{{ $positionTitle }} · {{ $departmentName }}</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-brand-50">
              <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">Employee ID: {{ $employeeId }}</span>
              <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">{{ $facilityName }}</span>
              @if($hireDate !== '—')
              <span class="rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/20">Hired {{ $hireDate }}</span>
              @endif
            </div>
          </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-3 xl:w-[520px]">
          <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/20">
            <p class="text-xs text-brand-100">Profile Complete</p>
            <p class="mt-1 text-2xl font-black">{{ $profileComplete }}%</p>
            <div class="mt-2 h-2 rounded-full bg-white/20"><div class="h-2 rounded-full bg-brand-300" style="width: {{ $profileComplete }}%"></div></div>
          </div>
          <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/20">
            <p class="text-xs text-brand-100">Facility</p>
            <p class="mt-1 text-lg font-black leading-tight">{{ $facilityName }}</p>
          </div>
          <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/20">
            <p class="text-xs text-brand-100">Account Email</p>
            <p class="mt-1 text-sm font-black leading-tight">{{ $user->email }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-6 grid gap-3 md:grid-cols-3">
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
      <p class="font-bold">Complete your profile</p>
      <p class="mt-1 text-amber-700">Review personal information and keep your contact details current.</p>
    </div>
    <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-900">
      <p class="font-bold">Employment documents</p>
      <p class="mt-1 text-brand-700">View and sign required forms in the Employment Portal.</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
      <p class="font-bold">Need help?</p>
      <p class="mt-1">Contact HR for updates to official employment records.</p>
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
          <button type="button" @click="activeTab='{{ $tab }}'" :class="activeTab==='{{ $tab }}' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left">
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
          <a href="{{ route('employment.portal') }}" class="rounded-2xl bg-brand-600 px-4 py-3 text-center text-white hover:bg-brand-700">Employment Portal</a>
          <a href="{{ route('pre-employment.portal') }}" class="rounded-2xl border border-brand-200 px-4 py-3 text-center text-brand-700 hover:bg-brand-50">Pre-Employment</a>
          <a href="{{ route('member.news-events.index') }}" class="rounded-2xl border border-brand-200 px-4 py-3 text-center text-brand-700 hover:bg-brand-50">News & Events</a>
          <a href="{{ route('settings.password') }}" class="rounded-2xl border border-brand-200 px-4 py-3 text-center text-brand-700 hover:bg-brand-50">Change Password</a>
        </div>
      </section>
      <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
        <h3 class="text-lg font-black text-slate-950">Account</h3>
        <div class="mt-5 space-y-3 text-sm">
          <div class="rounded-2xl bg-brand-50/60 p-4">
            <p class="text-xs font-bold uppercase text-brand-700">Login Email</p>
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
