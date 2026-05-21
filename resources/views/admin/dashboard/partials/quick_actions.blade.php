<section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card">
    <div class="border-b border-slate-200 bg-teal-50 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-950">Quick Actions</h2>
        <p class="mt-1 text-sm text-slate-500">Common administration tasks</p>
    </div>
    <div class="grid gap-3 p-6 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('admin.facilities.index') }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-teal-300 hover:bg-teal-50">
            <span class="text-2xl text-teal-600"><i class="fa-solid fa-building"></i></span>
            <p class="mt-3 font-bold text-slate-950 group-hover:text-teal-800">Manage Facilities</p>
            <p class="mt-1 text-xs text-slate-500">View, edit, and organize all facilities</p>
        </a>

        <a href="{{ route('admin.facilities.create') }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-teal-300 hover:bg-teal-50">
            <span class="text-2xl text-teal-600"><i class="fa-solid fa-plus"></i></span>
            <p class="mt-3 font-bold text-slate-950 group-hover:text-teal-800">Add New Facility</p>
            <p class="mt-1 text-xs text-slate-500">Create a new facility profile</p>
        </a>

        <a href="{{ route('admin.settings.index') }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-teal-300 hover:bg-teal-50">
            <span class="text-2xl text-teal-600"><i class="fa-solid fa-gear"></i></span>
            <p class="mt-3 font-bold text-slate-950 group-hover:text-teal-800">System Settings</p>
            <p class="mt-1 text-xs text-slate-500">Configure global settings</p>
        </a>
    </div>
</section>
