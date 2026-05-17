<section x-show="activeTab==='overview'" x-transition class="space-y-6">
  <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-lg font-black text-slate-950">Employee Record Overview</h3>
        <p class="text-sm text-slate-500">A compact summary of your official employee master record.</p>
      </div>
      <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Verified</span>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
      <div class="rounded-3xl bg-teal-50/60 p-5">
        <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Primary Facility</p>
        <p class="mt-2 text-lg font-black text-slate-950">{{ $facilityName }}</p>
      </div>
      <div class="rounded-3xl bg-teal-50/60 p-5">
        <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Position</p>
        <p class="mt-2 text-lg font-black text-slate-950">{{ $positionTitle }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ $departmentName }}</p>
      </div>
      <div class="rounded-3xl bg-teal-50/60 p-5">
        <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Employment</p>
        <p class="mt-2 text-lg font-black text-slate-950">Employee ID {{ $employeeId }}</p>
        @if($hireDate !== '—')
        <p class="mt-1 text-sm text-slate-500">Hired {{ $hireDate }}</p>
        @endif
      </div>
      <div class="rounded-3xl bg-teal-50/60 p-5">
        <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Record Ownership</p>
        <p class="mt-2 text-lg font-black text-slate-950">Employee + HR</p>
        <p class="mt-1 text-sm text-slate-500">Sensitive fields require HR approval.</p>
      </div>
    </div>
  </div>
</section>

<section x-show="activeTab==='personal'" x-transition class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
  <div class="flex items-center justify-between">
    <div>
      <h3 class="text-lg font-black text-slate-950">Personal Information</h3>
      <p class="text-sm text-slate-500">Update your account name and email used to sign in.</p>
    </div>
    <button type="button" @click="editMode = !editMode" class="rounded-2xl border border-teal-200 px-4 py-2 text-sm font-bold text-teal-700 hover:bg-teal-50" x-text="editMode ? 'Cancel' : 'Edit'"></button>
  </div>
  <form x-ref="profileForm" method="POST" action="{{ route('settings.profile.update') }}" class="mt-6">
    @csrf
    @method('PATCH')
    <div class="grid gap-4 sm:grid-cols-2">
      <label class="block sm:col-span-2">
        <span class="text-sm font-bold text-slate-700">Full Name</span>
        <input name="name" :disabled="!editMode" value="{{ old('name', $user->name) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/20 disabled:text-slate-500" />
        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
      </label>
      <label class="block sm:col-span-2">
        <span class="text-sm font-bold text-slate-700">Email</span>
        <input name="email" type="email" :disabled="!editMode" value="{{ old('email', $user->email) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-500/20 disabled:text-slate-500" />
        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
      </label>
      @if($employee)
      <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2">
        <p class="text-xs font-bold uppercase text-slate-500">HR Record Name</p>
        <p class="mt-1 font-semibold text-slate-900">{{ $displayName }}</p>
        <p class="mt-1 text-xs text-slate-500">Legal name on file with HR. Contact HR to update official records.</p>
      </div>
      @endif
    </div>
  </form>
</section>

<section x-show="activeTab==='employment'" x-transition class="space-y-6">
  <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
    <h3 class="text-lg font-black text-slate-950">Employment Record</h3>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
      <div class="rounded-2xl bg-teal-50/60 p-4"><p class="text-xs font-bold uppercase text-teal-700">Employee ID</p><p class="mt-1 font-black text-slate-950">{{ $employeeId }}</p></div>
      <div class="rounded-2xl bg-teal-50/60 p-4"><p class="text-xs font-bold uppercase text-teal-700">Hire Date</p><p class="mt-1 font-black text-slate-950">{{ $hireDate }}</p></div>
      <div class="rounded-2xl bg-teal-50/60 p-4"><p class="text-xs font-bold uppercase text-teal-700">Job Title</p><p class="mt-1 font-black text-slate-950">{{ $positionTitle }}</p></div>
      <div class="rounded-2xl bg-teal-50/60 p-4"><p class="text-xs font-bold uppercase text-teal-700">Department</p><p class="mt-1 font-black text-slate-950">{{ $departmentName }}</p></div>
      <div class="rounded-2xl bg-teal-50/60 p-4 sm:col-span-2"><p class="text-xs font-bold uppercase text-teal-700">Facility</p><p class="mt-1 font-black text-slate-950">{{ $facilityName }}</p></div>
    </div>
  </div>
  <p class="text-sm text-slate-500">For detailed employment documents and forms, visit the <a href="{{ route('employment.portal') }}" class="font-bold text-teal-600 hover:text-teal-800">Employment Portal</a>.</p>
</section>

<section x-show="activeTab==='certifications'" x-transition class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
  <h3 class="text-lg font-black text-slate-950">Licenses & Certifications</h3>
  <p class="mt-2 text-sm text-slate-500">Certification records will appear here when linked to your employee file. Upload renewals through the Employment Portal or contact HR.</p>
  <a href="{{ route('employment.portal') }}" class="mt-4 inline-block rounded-2xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700">Open Employment Portal</a>
</section>

<section x-show="activeTab==='documents'" x-transition class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
  <h3 class="text-lg font-black text-slate-950">Employee Document File</h3>
  <p class="mt-2 text-sm text-slate-500">Official documents, forms, and signatures are managed in the Employment Portal.</p>
  <a href="{{ route('employment.portal') }}" class="mt-4 inline-block rounded-2xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700">View Documents</a>
</section>

<section x-show="activeTab==='competencies'" x-transition class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
  <h3 class="text-lg font-black text-slate-950">Competency Skills & Reviews</h3>
  <p class="mt-2 text-sm text-slate-500">Competency reviews are official HR/supervisor records. Employees can view completed reviews but cannot edit them.</p>
  <div class="mt-5 rounded-3xl border border-teal-100 bg-teal-50/50 p-5">
    <p class="font-black text-slate-950">Read-Only Access</p>
    <p class="mt-1 text-sm text-slate-500">Ask your supervisor or HR if you need clarification on a competency review.</p>
  </div>
</section>

<section x-show="activeTab==='history'" x-transition class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
  <h3 class="text-lg font-black text-slate-950">History & Audit Log</h3>
  <p class="text-sm text-slate-500">A permanent record of changes and HR actions will appear here as activity is logged.</p>
</section>

<section x-show="activeTab==='security'" x-transition class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
  <h3 class="text-lg font-black text-slate-950">Security Settings</h3>
  <p class="text-sm text-slate-500">Account protection and sensitive record access.</p>
  <div class="mt-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-teal-50/60 p-4">
      <div>
        <p class="font-bold text-slate-950">Password</p>
        <p class="text-sm text-slate-500">Update your sign-in password.</p>
      </div>
      <a href="{{ route('settings.password') }}" class="rounded-xl border border-teal-200 bg-white px-3 py-2 text-sm font-bold text-teal-700">Change</a>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-teal-50/60 p-4">
      <div>
        <p class="font-bold text-slate-950">Appearance</p>
        <p class="text-sm text-slate-500">Theme and display preferences.</p>
      </div>
      <a href="{{ route('settings.appearance') }}" class="rounded-xl border border-teal-200 bg-white px-3 py-2 text-sm font-bold text-teal-700">Manage</a>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="rounded-2xl bg-slate-50 p-4">
      @csrf
      <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700">Sign out</button>
    </form>
  </div>
</section>
