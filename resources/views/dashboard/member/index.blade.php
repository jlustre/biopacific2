@extends('layouts.member-portal')

@php
    $newsEventsCount = $newsEventsCount ?? 0;
    $todayLabel = $todayLabel ?? now()->format('l, F j, Y');
    $complianceScore = $stats['employee_file_verified'] ?? null;
    $documentsNeededCount = $stats['documents_needed'] ?? 0;
@endphp

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
        <!-- Hero / Overview -->
        <div class="grid gap-6 xl:grid-cols-12">
          <div class="relative overflow-hidden rounded-[2rem] bg-teal-700 text-white shadow-soft xl:col-span-8">
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-teal-600/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-12 h-56 w-56 rounded-full bg-teal-800/50" aria-hidden="true"></div>
            <div class="relative z-10 flex flex-col gap-6 p-6 md:flex-row md:items-center md:justify-between">
              <div>
                <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">Today • {{ $todayLabel }}</div>
                <h2 class="mt-5 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">Your next shift starts at 7:00 AM</h2>
                <p class="mt-3 max-w-2xl text-teal-50">Assigned to Skilled Nursing Wing A. Please review the updated fall prevention policy before clocking in.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                  <button type="button" class="rounded-2xl bg-white px-5 py-3 text-sm font-bold text-teal-800 shadow-lg hover:bg-teal-50">Clock In</button>
                  <a href="{{ route('member.schedule') }}" class="rounded-2xl bg-white/10 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/20 hover:bg-white/20">View Schedule</a>
                  <button type="button" class="rounded-2xl bg-white/10 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/20 hover:bg-white/20">Message Supervisor</button>
                </div>
              </div>
              <div class="rounded-3xl bg-white/10 p-5 ring-1 ring-white/20 md:min-w-64">
                <p class="text-sm text-teal-100">Current Compliance Score</p>
                <div class="mt-4 flex items-end gap-2">
                  @if($complianceScore !== null)
                    <span class="text-5xl font-black">{{ $complianceScore }}%</span>
                    <span class="mb-2 rounded-full px-2 py-1 text-xs font-bold {{ $complianceScore >= 90 ? 'bg-emerald-400/20 text-emerald-100' : ($complianceScore >= 70 ? 'bg-amber-400/20 text-amber-100' : 'bg-rose-400/20 text-rose-100') }}">
                      {{ $complianceScore >= 90 ? 'Good' : ($complianceScore >= 70 ? 'Fair' : 'Needs work') }}
                    </span>
                  @else
                    <span class="text-5xl font-black">&mdash;</span>
                  @endif
                </div>
                <div class="mt-4 h-3 rounded-full bg-white/20">
                  <div class="h-3 rounded-full {{ ($complianceScore ?? 0) >= 90 ? 'bg-emerald-300' : (($complianceScore ?? 0) >= 70 ? 'bg-amber-300' : 'bg-rose-300') }}" style="width: {{ $complianceScore !== null ? min(100, max(0, $complianceScore)) : 0 }}%"></div>
                </div>
                <p class="mt-3 text-xs text-teal-100">
                  @if($documentsNeededCount > 0)
                    {{ $documentsNeededCount }} checklist {{ Str::plural('item', $documentsNeededCount) }} need attention.
                  @elseif($complianceScore !== null)
                    Employee file checklist is up to date.
                  @else
                    Link your employee record to track compliance.
                  @endif
                </p>
              </div>
            </div>
          </div>

          <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card xl:col-span-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500">PTO Balance</p>
                <p class="mt-1 text-3xl font-black text-slate-950">64.5 hrs</p>
              </div>
              <div class="rounded-2xl bg-emerald-50 p-3 text-2xl">🌴</div>
            </div>
            <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs">
              <div class="rounded-2xl bg-slate-50 p-3"><p class="font-bold text-slate-950">32</p><p class="text-slate-500">Vacation</p></div>
              <div class="rounded-2xl bg-slate-50 p-3"><p class="font-bold text-slate-950">20</p><p class="text-slate-500">Sick</p></div>
              <div class="rounded-2xl bg-slate-50 p-3"><p class="font-bold text-slate-950">12.5</p><p class="text-slate-500">Holiday</p></div>
            </div>
            <button type="button" class="mt-5 w-full rounded-2xl bg-brand-600 px-4 py-3 text-sm font-bold text-white hover:bg-brand-700">Request Time Off</button>
          </div>
        </div>

        <!-- Metric Cards -->
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
            <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Upcoming Shift</p><span class="rounded-xl bg-brand-50 p-2 text-brand-700"><i class="fa-solid fa-calendar-day"></i></span></div>
            <p class="mt-4 text-2xl font-black text-slate-950">7:00 AM</p>
            <p class="text-sm text-slate-500">Skilled Nursing Wing A</p>
          </div>
          <a href="{{ route('member.trainings') }}" class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-card transition hover:border-brand-300 hover:shadow-md">
            <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Trainings</p><span class="rounded-xl bg-amber-50 p-2 text-amber-700"><i class="fa-solid fa-graduation-cap"></i></span></div>
            <p class="mt-4 text-2xl font-black text-slate-950 group-hover:text-brand-700">{{ $stats['trainings_needs_action'] ?? 0 }}</p>
            <p class="text-sm font-semibold text-brand-600 group-hover:text-brand-700">
              @if(($stats['trainings_pending_signature'] ?? 0) > 0)
                {{ $stats['trainings_pending_signature'] }} need signature ·
              @endif
              View all &rarr;
            </p>
          </a>
          <a href="{{ route('member.certifications') }}" class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-card transition hover:border-brand-300 hover:shadow-md">
            <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-500">Expiring Certifications</p><span class="rounded-xl bg-rose-50 p-2 text-rose-700"><i class="fa-solid fa-award"></i></span></div>
            <p class="mt-4 text-2xl font-black text-slate-950 group-hover:text-brand-700">{{ ($stats['certifications_expiring'] ?? 0) + ($stats['certifications_expired'] ?? 0) }}</p>
            <p class="text-sm font-semibold text-brand-600 group-hover:text-brand-700">View all &rarr;</p>
          </a>
          <a href="{{ route('member.news-events.index') }}" class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-card transition hover:border-brand-300 hover:shadow-md">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-slate-500">News & Events</p>
              <span class="rounded-xl bg-brand-50 p-2 text-brand-700"><i class="fa-solid fa-newspaper"></i></span>
            </div>
            <p class="mt-4 text-2xl font-black text-slate-950 group-hover:text-brand-700">{{ $newsEventsCount ?? 0 }}</p>
            <p class="text-sm font-semibold text-brand-600 group-hover:text-brand-700">View all &rarr;</p>
          </a>
        </div>

        <!-- Content Grid -->
        <div class="mt-6 grid gap-6 xl:grid-cols-12">
          <!-- Left content -->
          <div class="space-y-6 xl:col-span-8">
            <!-- Quick Actions -->
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
              <div class="mb-5 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-bold text-slate-950">Quick Actions</h2>
                  <p class="text-sm text-slate-500">Common employee self-service tasks</p>
                </div>
              </div>
              <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <button type="button" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left hover:border-brand-300 hover:bg-brand-50"><span class="text-2xl text-brand-600"><i class="fa-solid fa-upload"></i></span><p class="mt-3 font-bold">Upload Document</p><p class="text-xs text-slate-500">License, forms, IDs</p></button>
                <button type="button" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left hover:border-brand-300 hover:bg-brand-50"><span class="text-2xl text-brand-600"><i class="fa-solid fa-pen"></i></span><p class="mt-3 font-bold">Sign Forms</p><p class="text-xs text-slate-500">Pending eSignatures</p></button>
                <button type="button" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left hover:border-brand-300 hover:bg-brand-50"><span class="text-2xl">🌴</span><p class="mt-3 font-bold">Request PTO</p><p class="text-xs text-slate-500">Leave request</p></button>
                <button type="button" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left hover:border-brand-300 hover:bg-brand-50"><span class="text-2xl">🎫</span><p class="mt-3 font-bold">Open Ticket</p><p class="text-xs text-slate-500">HR or IT support</p></button>
              </div>
            </section>

            <!-- Today Schedule -->
            <section id="schedule" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
              <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h2 class="text-lg font-bold text-slate-950">Today's Schedule</h2>
                  <p class="text-sm text-slate-500">Shift details, break tracking, and supervisor notes</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Scheduled</span>
              </div>
              <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-3xl bg-brand-50 p-5">
                  <p class="text-sm font-medium text-brand-700">Shift Time</p>
                  <p class="mt-2 text-2xl font-black text-brand-950">7:00 AM - 3:30 PM</p>
                  <p class="mt-1 text-sm text-brand-700">30 min unpaid meal break</p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-5">
                  <p class="text-sm font-medium text-slate-500">Assignment</p>
                  <p class="mt-2 text-xl font-black text-slate-950">Wing A • Room 101-118</p>
                  <p class="mt-1 text-sm text-slate-500">Charge Nurse: Linda Chen</p>
                </div>
                <div class="rounded-3xl bg-amber-50 p-5">
                  <p class="text-sm font-medium text-amber-700">Shift Note</p>
                  <p class="mt-2 text-sm font-semibold text-amber-900">Fall-risk patients updated. Review assignment sheet before rounds.</p>
                </div>
              </div>
            </section>

          </div>

          <!-- Right content -->
          <aside class="space-y-6 xl:col-span-4">
            <!-- Profile Card -->
            <section id="profile" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
              <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-100 text-2xl font-black text-brand-800">{{ $initials }}</div>
                <div>
                  <h2 class="text-xl font-black text-slate-950">{{ $displayName }}</h2>
                  <p class="text-sm text-slate-500">{{ $positionTitle }}</p>
                  <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active Employee</span>
                </div>
              </div>
              <div class="mt-6 grid gap-3 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Employee ID</span><span class="font-bold">{{ $employeeId }}</span></div>
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Department</span><span class="font-bold">{{ $departmentName }}</span></div>
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Facility</span><span class="font-bold">{{ $facilityName }}</span></div>
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Hire Date</span><span class="font-bold">{{ $hireDate }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Classification</span><span class="font-bold">Full-Time</span></div>
              </div>
              <a href="{{ route('settings.profile') }}" class="mt-6 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-bold hover:bg-slate-50">Edit Profile</a>
            </section>

            <!-- Announcements -->
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
              <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-950">Announcements</h2>
                <a href="#" class="text-sm font-bold text-brand-600">All</a>
              </div>
              <div class="space-y-4">
                <article class="rounded-2xl bg-brand-50 p-4">
                  <p class="text-xs font-bold uppercase text-brand-700">Facility Update</p>
                  <h3 class="mt-1 font-bold text-slate-950">New fall prevention policy</h3>
                  <p class="mt-1 text-sm text-slate-600">Please acknowledge by May 22.</p>
                </article>
                <article class="rounded-2xl bg-slate-50 p-4">
                  <p class="text-xs font-bold uppercase text-slate-500">Company News</p>
                  <h3 class="mt-1 font-bold text-slate-950">Benefits open enrollment reminder</h3>
                  <p class="mt-1 text-sm text-slate-600">Enrollment window opens June 1.</p>
                </article>
              </div>
            </section>

          </aside>
        </div>
      </section>
@endsection
