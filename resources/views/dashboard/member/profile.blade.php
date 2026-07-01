@extends('layouts.member-portal')

@section('content')
<div x-data="{
  section: 'overview',
  editing: false,
  emergencyModalOpen: false,
  emergencyEditingId: null,
  emergencyRelationshipOptions: @js(\App\Models\MemberEmergencyContact::relationshipOptions()),
  init() {
    const hash = window.location.hash.replace('#', '');
    if (['overview', 'account', 'contact', 'emergency-contacts', 'security'].includes(hash)) {
      this.section = hash;
    }
    @if($errors->any() && old('_emergency_form'))
    this.section = 'emergency-contacts';
    this.emergencyModalOpen = true;
    @elseif($errors->has('avatar') || in_array(session('status'), ['avatar-updated', 'avatar-removed'], true))
    this.section = 'account';
    @elseif($errors->any() || session('status') === 'profile-updated')
    this.section = 'account';
    this.editing = true;
    @elseif(in_array(session('status'), ['emergency-contact-saved', 'emergency-contact-deleted', 'emergency-contact-primary'], true))
    this.section = 'emergency-contacts';
    @endif
  },
  startEdit() {
    this.section = 'account';
    this.editing = true;
    window.location.hash = 'account';
    this.$nextTick(() => {
      if (this.$refs.accountPanel) {
        this.$refs.accountPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      if (this.$refs.accountNameInput) {
        this.$refs.accountNameInput.focus();
      }
    });
  },
  cancelEdit() {
    this.editing = false;
  },
  openEmergencyAdd() {
    this.emergencyEditingId = null;
    this.clearEmergencyForm();
    this.emergencyModalOpen = true;
  },
  openEmergencyEdit(contact) {
    this.emergencyEditingId = contact.id;
    this.emergencyModalOpen = true;
    this.$nextTick(() => {
      this.$refs.emFirstName.value = contact.first_name || '';
      this.$refs.emLastName.value = contact.last_name || '';
      const relationshipSelect = this.$refs.emRelationship;
      const relationship = contact.relationship || '';
      if (relationshipSelect) {
        relationshipSelect.querySelectorAll('option[data-legacy]').forEach((el) => el.remove());
        if (relationship && !this.emergencyRelationshipOptions.includes(relationship)) {
          const legacy = document.createElement('option');
          legacy.value = relationship;
          legacy.textContent = relationship;
          legacy.dataset.legacy = '1';
          relationshipSelect.appendChild(legacy);
        }
        relationshipSelect.value = relationship;
      }
      this.$refs.emPhone.value = contact.phone || '';
      this.$refs.emEmail.value = contact.email || '';
      this.$refs.emAddress1.value = contact.address1 || '';
      this.$refs.emAddress2.value = contact.address2 || '';
      this.$refs.emCity.value = contact.city || '';
      this.$refs.emState.value = contact.state || '';
      this.$refs.emZip.value = contact.zip || '';
      this.$refs.emIsPrimary.checked = !!contact.is_primary;
    });
  },
  closeEmergencyModal() {
    this.emergencyModalOpen = false;
    this.emergencyEditingId = null;
  },
  clearEmergencyForm() {
    this.$nextTick(() => {
      ['emFirstName','emLastName','emRelationship','emPhone','emEmail','emAddress1','emAddress2','emCity','emState','emZip'].forEach((key) => {
        if (this.$refs[key]) this.$refs[key].value = '';
      });
      if (this.$refs.emIsPrimary) this.$refs.emIsPrimary.checked = false;
    });
  }
}">
<section class="mx-auto max-w-5xl px-4 py-5 pb-24 sm:px-6 lg:py-8 lg:pb-10">
  {{-- Compact hero --}}
  <div class="relative overflow-hidden rounded-3xl border border-teal-100/80 bg-gradient-to-br from-teal-600 via-teal-700 to-slate-800 text-white shadow-lg shadow-teal-900/10">
    <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute bottom-0 left-1/4 h-24 w-48 rounded-full bg-teal-400/20 blur-xl" aria-hidden="true"></div>
    <div class="relative flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
      <div class="flex items-center gap-4">
        <div class="relative shrink-0">
          @include('dashboard.member.partials.user-avatar', [
              'avatarUrl' => $avatarUrl ?? null,
              'initials' => $initials,
              'size' => 'hero',
              'shape' => 'rounded-2xl',
              'variant' => 'hero',
              'ring' => 'ring-2 ring-white/25',
          ])
          <span class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-400 text-[10px] text-emerald-950 ring-2 ring-teal-700" title="Active account">✓</span>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wider text-teal-100/90">My Profile</p>
          <h1 class="truncate text-2xl font-black tracking-tight sm:text-3xl">{{ $displayName }}</h1>
          <p class="mt-0.5 truncate text-sm text-teal-50/90">{{ $user->email }}</p>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-3 sm:justify-end">
        <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-2.5 ring-1 ring-white/15">
          <svg class="h-11 w-11 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
            <circle cx="18" cy="18" r="15.5" fill="none" class="stroke-white/20" stroke-width="3"/>
            <circle cx="18" cy="18" r="15.5" fill="none" class="stroke-teal-300" stroke-width="3" stroke-linecap="round"
                    stroke-dasharray="{{ $profileComplete }}, 100"/>
          </svg>
          <div>
            <p class="text-[10px] font-bold uppercase tracking-wide text-teal-100">Complete</p>
            <p class="text-lg font-black leading-none">{{ $profileComplete }}%</p>
          </div>
        </div>
        <button type="button" @click="startEdit()"
                class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-teal-800 shadow-sm hover:bg-teal-50">
          Edit account
        </button>
      </div>
    </div>
  </div>

  {{-- Section pills --}}
  <nav class="mt-4 flex gap-1 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-1 shadow-sm" aria-label="Profile sections">
    @foreach([
      ['overview', 'Overview', 'fa-house'],
      ['account', 'Account', 'fa-user-pen'],
      ['contact', 'Contact', 'fa-address-book'],
      ['emergency-contacts', 'Emergency Contacts', 'fa-truck-medical'],
      ['security', 'Security', 'fa-shield-halved'],
    ] as [$id, $label, $icon])
    <button type="button"
            @click="section='{{ $id }}'"
            :class="section === '{{ $id }}' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'"
            class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition">
      <i class="fa-solid {{ $icon }} text-xs opacity-80"></i>
      {{ $label }}
    </button>
    @endforeach
  </nav>

  @if(session('status') === 'MFA enabled successfully.')
  <div class="mt-4 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
    <i class="fa-solid fa-circle-check"></i> Multi-factor authentication is now enabled on your account.
  </div>
  @endif
  @if(session('status') === 'profile-updated')
  <div class="mt-4 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
    <i class="fa-solid fa-circle-check"></i> Your account details were saved.
  </div>
  @endif
  @if(session('status') === 'emergency-contact-saved')
  <div class="mt-4 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
    <i class="fa-solid fa-circle-check"></i> Emergency contact saved.
  </div>
  @endif
  @if(session('status') === 'emergency-contact-deleted')
  <div class="mt-4 flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
    Emergency contact removed.
  </div>
  @endif
  @if(session('status') === 'emergency-contact-primary')
  <div class="mt-4 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
    Primary emergency contact updated.
  </div>
  @endif

  {{-- Overview --}}
  <div x-show="section === 'overview'" x-cloak class="mt-4 space-y-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Sign-in email</p>
        <p class="mt-1 truncate text-sm font-bold text-slate-900">{{ $user->email }}</p>
        <p class="mt-1 text-xs {{ $emailVerified ? 'text-emerald-600' : 'text-amber-600' }}">
          {{ $emailVerified ? 'Verified' : 'Verification pending' }}
        </p>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Member since</p>
        <p class="mt-1 text-sm font-bold text-slate-900">{{ $memberSince ?? '—' }}</p>
        <p class="mt-1 text-xs text-slate-500">Portal account</p>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Phone</p>
        <p class="mt-1 text-sm font-bold text-slate-900">{{ $personalPhone ?? 'Not on file' }}</p>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Last updated</p>
        <p class="mt-1 text-sm font-bold text-slate-900">{{ $lastUpdated ?? '—' }}</p>
      </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      @include('dashboard.member.partials.profile-panel-expirations', ['upcomingExpirations' => $upcomingExpirations ?? []])
      @include('dashboard.member.partials.profile-panel-recognitions', ['profileRecognitions' => $profileRecognitions ?? []])
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-black text-slate-900">About you</h2>
        <dl class="mt-4 space-y-3 text-sm">
          <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
            <dt class="text-slate-500">Display name</dt>
            <dd class="text-right font-semibold text-slate-900">{{ $user->name }}</dd>
          </div>
          @if($legalName && $legalName !== $user->name)
          <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
            <dt class="text-slate-500">Legal name (HR)</dt>
            <dd class="text-right font-semibold text-slate-900">{{ $legalName }}</dd>
          </div>
          @endif
          @if($dateOfBirth)
          <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
            <dt class="text-slate-500">Date of birth</dt>
            <dd class="text-right font-semibold text-slate-900">{{ $dateOfBirth }}</dd>
          </div>
          @endif
          <div class="flex justify-between gap-4">
            <dt class="text-slate-500">Profile strength</dt>
            <dd class="text-right font-semibold text-teal-700">{{ $profileComplete }}% complete</dd>
          </div>
        </dl>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-black text-slate-900">Complete your profile</h2>
        <ul class="mt-4 space-y-2.5 text-sm">
          @php
            $checklist = [
              ['done' => filled($user->name), 'label' => 'Add your display name'],
              ['done' => filled($user->email), 'label' => 'Confirm sign-in email'],
              ['done' => $emailVerified, 'label' => 'Verify your email address'],
              ['done' => filled($personalPhone), 'label' => 'Phone number on file with HR'],
              ['done' => filled($personalAddress), 'label' => 'Mailing address on file with HR'],
            ];
          @endphp
          @foreach($checklist as $item)
          <li class="flex items-start gap-2.5">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] {{ $item['done'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
              <i class="fa-solid {{ $item['done'] ? 'fa-check' : 'fa-minus' }}"></i>
            </span>
            <span class="{{ $item['done'] ? 'text-slate-600 line-through decoration-slate-300' : 'font-medium text-slate-800' }}">{{ $item['label'] }}</span>
          </li>
          @endforeach
        </ul>
        @if($profileComplete < 100)
        <p class="mt-4 text-xs leading-relaxed text-slate-500">Contact HR to update phone or address on your official record. You can change your portal name and email under Account.</p>
        @endif
      </div>
    </div>
  </div>

  {{-- Account --}}
  <div x-show="section === 'account'" x-cloak class="mt-4" x-ref="accountPanel">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-black text-slate-900">Account details</h2>
          <p class="text-sm text-slate-500">How you appear and sign in to this portal.</p>
        </div>
        <button type="button" x-show="!editing" @click="editing = true"
                class="rounded-xl border border-teal-200 px-3 py-2 text-sm font-bold text-teal-700 hover:bg-teal-50">
          Edit
        </button>
      </div>

      @include('dashboard.member.partials.profile-avatar-upload', [
          'avatarUrl' => $avatarUrl ?? null,
          'initials' => $initials,
      ])

      <form method="POST" action="{{ route('settings.profile.update') }}" class="mt-5">
        @csrf
        @method('PATCH')
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="block sm:col-span-2">
            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Full name</span>
                 <input name="name" value="{{ old('name', $user->name) }}" required :disabled="!editing" x-ref="accountNameInput"
                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-medium text-slate-900 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-500/15 disabled:cursor-default disabled:opacity-70"/>
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
          </label>
          <label class="block sm:col-span-2">
            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Email address</span>
            <input name="email" type="email" value="{{ old('email', $user->email) }}" required :disabled="!editing"
                   class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-medium text-slate-900 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-500/15 disabled:cursor-default disabled:opacity-70"/>
            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            <p class="mt-1.5 text-xs text-slate-500">Used for login and important account notifications.</p>
          </label>
        </div>
        <div x-show="editing" class="mt-5 flex flex-wrap gap-2">
          <button type="submit" class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-700">Save changes</button>
          <button type="button" @click="cancelEdit()" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
        </div>
      </form>

      @if($legalName)
      <div class="mt-5 rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
        <p class="font-semibold text-slate-800">Legal name on HR record</p>
        <p class="mt-0.5">{{ $legalName }}</p>
        <p class="mt-1 text-xs text-slate-500">To change your legal name, contact HR — it is not editable here.</p>
      </div>
      @endif
    </div>
  </div>

  {{-- Emergency contacts --}}
  <div x-show="section === 'emergency-contacts'" x-cloak class="mt-4" id="emergency-contacts">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      @include('dashboard.member.partials.profile-emergency-contacts')
    </div>
  </div>

  {{-- Contact --}}
  <div x-show="section === 'contact'" x-cloak class="mt-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-base font-black text-slate-900">Contact information</h2>
      <p class="mt-1 text-sm text-slate-500">Personal contact details from your HR record (read-only here).</p>
      <dl class="mt-5 divide-y divide-slate-100">
        <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between">
          <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Mobile / phone</dt>
          <dd class="text-sm font-semibold text-slate-900">{{ $personalPhone ?? '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between">
          <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Mailing address</dt>
          <dd class="max-w-md text-right text-sm font-semibold text-slate-900 sm:text-left">{{ $personalAddress ?? '—' }}</dd>
        </div>
        <div class="flex flex-col gap-1 py-4 sm:flex-row sm:justify-between">
          <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Portal email</dt>
          <dd class="text-sm font-semibold text-slate-900">{{ $user->email }}</dd>
        </div>
      </dl>
      <p class="mt-2 text-xs leading-relaxed text-slate-500">Need to update phone or address? Reach out to HR — they maintain your official personnel file.</p>
    </div>
  </div>

  {{-- Security --}}
  <div x-show="section === 'security'" x-cloak class="mt-4 space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-base font-black text-slate-900">Password</h2>
      <p class="mt-1 text-sm text-slate-500">Keep your account secure with a strong, unique password.</p>
      <a href="{{ route('settings.password') }}"
         class="mt-4 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">
        <i class="fa-solid fa-key text-xs"></i> Change password
      </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-base font-black text-slate-900">Appearance</h2>
      <p class="mt-1 text-sm text-slate-500">Theme and display preferences for your portal experience.</p>
      <a href="{{ route('settings.appearance') }}"
         class="mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-800 hover:bg-slate-50">
        <i class="fa-solid fa-palette text-xs text-teal-600"></i> Display settings
      </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <h2 class="text-base font-black text-slate-900">Session</h2>
      <p class="mt-1 text-sm text-slate-500">Sign out of this device when you are finished.</p>
      <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-800 hover:bg-rose-100">
          <i class="fa-solid fa-right-from-bracket text-xs"></i> Sign out
        </button>
      </form>
    </div>
  </div>
</section>
</div>
@endsection

@section('mobile-nav')
@include('dashboard.member.partials.portal-profile-mobile-nav')
@endsection
