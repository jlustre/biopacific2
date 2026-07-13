@extends('layouts.member-portal')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 sm:text-2xl">Facility Leadership</h1>
            <p class="mt-1 text-sm text-slate-600">
                @if(!$facility)
                No facility is available for your account.
                @elseif(!empty($canEdit))
                Highest rank first. Names are auto-filled from current HR assignments, then saved roster values.
                @else
                Highest rank first. You have read-only access to this facility’s leadership roster.
                @endif
            </p>
        </div>

        @if(($facilities ?? collect())->isNotEmpty())
        <div class="w-full sm:w-72">
            <label for="leadership-facility-select" class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Facility</label>
            <select id="leadership-facility-select"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    onchange="if (this.value) window.location.href = this.value;">
                @foreach($facilities as $optionFacility)
                <option value="{{ route('admin.facility.leadership.edit', ['facility' => $optionFacility->getRouteKey()]) }}"
                    @selected($facility && (int) $facility->id === (int) $optionFacility->id)>
                    {{ $optionFacility->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
    @endif

    @if(!$facility)
    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
        No facilities are available for your account.
    </div>
    @else
    @if(empty($canEdit))
    <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
        <i class="fas fa-eye mr-1 text-slate-500"></i> Viewing only — contact your facility administrator or DSD to request changes.
    </div>
    @endif

    @if(!empty($canEdit))
    <form method="POST" action="{{ route('admin.facility.leadership.update', ['facility' => $facility->getRouteKey()]) }}" class="space-y-4">
        @csrf
        @method('PUT')
    @endif

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden"
             x-data="{
                contacts: @js(($employeeOptions ?? collect())->mapWithKeys(fn ($o) => [strtolower(trim($o['value'] ?? '')) => ['email' => $o['email'] ?? null, 'phone' => $o['phone'] ?? null]])->all()),
                names: @js(collect($rows)->where('is_custom', false)->mapWithKeys(fn ($r) => [$r['role_key'] => old('leadership.'.$r['role_key'], $r['name'] ?? '')])->all()),
                contactFor(name) {
                    const key = (name || '').trim().toLowerCase();
                    return key && this.contacts[key] ? this.contacts[key] : { email: null, phone: null };
                },
                display(value) {
                    return value && String(value).trim() !== '' ? value : '—';
                }
             }">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-slate-900">Standard leadership roles</h2>
                @if($canRemoveRoles ?? false)
                <p class="mt-1 text-[11px] text-slate-500">Vacant roles with no HR assignment at this facility can be removed.</p>
                @endif
            </div>
            <div class="hidden border-b border-slate-100 bg-slate-50 px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500 lg:grid lg:grid-cols-[8.5rem_minmax(10rem,1fr)_minmax(10rem,14rem)_minmax(11rem,1fr)_minmax(8rem,10rem)_auto] lg:gap-x-4">
                <span>Role</span>
                <span>Title</span>
                <span>Name</span>
                <span>Company email</span>
                <span>Phone</span>
                <span></span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($rows as $row)
                    @if($row['is_custom'])
                        @continue
                    @endif
                    <div class="grid grid-cols-1 gap-2 px-4 py-3 lg:grid-cols-[8.5rem_minmax(10rem,1fr)_minmax(10rem,14rem)_minmax(11rem,1fr)_minmax(8rem,10rem)_auto] lg:items-center lg:gap-x-4">
                        <div class="min-w-0 shrink-0">
                            <span class="inline-flex max-w-full rounded bg-teal-50 px-2 py-0.5 text-[10px] font-black uppercase leading-tight text-teal-800 ring-1 ring-teal-100">{{ $row['abbrev'] }}</span>
                        </div>
                        <label class="min-w-0 text-sm font-semibold text-slate-700" @if(!empty($canEdit)) for="leadership-{{ $row['role_key'] }}" @endif>{{ $row['role_label'] }}</label>
                        <div class="min-w-0">
                            @if(!empty($canEdit))
                            <div @change="names['{{ $row['role_key'] }}'] = $event.target.value">
                                <x-admin.facilities.leadership.name-select
                                    id="leadership-{{ $row['role_key'] }}"
                                    name="leadership[{{ $row['role_key'] }}]"
                                    :value="old('leadership.'.$row['role_key'], $row['name'])"
                                    :employee-options="$employeeOptions" />
                            </div>
                            @else
                            <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                                {{ $row['name'] !== '' && $row['name'] !== null ? $row['name'] : '— Vacant —' }}
                            </p>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 lg:hidden">Company email</p>
                            @if(!empty($canEdit))
                            <p class="truncate text-sm text-slate-700" x-text="display(contactFor(names['{{ $row['role_key'] }}']).email)"></p>
                            @else
                            <p class="truncate text-sm text-slate-700">
                                @if(!empty($row['email']))
                                <a href="mailto:{{ $row['email'] }}" class="text-teal-700 hover:underline">{{ $row['email'] }}</a>
                                @else
                                —
                                @endif
                            </p>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 lg:hidden">Phone</p>
                            @if(!empty($canEdit))
                            <p class="truncate text-sm text-slate-700" x-text="display(contactFor(names['{{ $row['role_key'] }}']).phone)"></p>
                            @else
                            <p class="truncate text-sm text-slate-700">
                                @if(!empty($row['phone']))
                                <a href="tel:{{ preg_replace('/\D+/', '', $row['phone']) }}" class="text-teal-700 hover:underline">{{ $row['phone'] }}</a>
                                @else
                                —
                                @endif
                            </p>
                            @endif
                        </div>
                        @if(($canRemoveRoles ?? false) && !empty($row['can_delete']))
                        <div class="flex justify-end">
                            <form method="POST"
                                  action="{{ route('admin.facility.leadership.role.destroy', ['facility' => $facility->getRouteKey(), 'roleKey' => $row['role_key']]) }}"
                                  onsubmit="return confirm('Remove the {{ addslashes($row['role_label']) }} role from this facility?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs font-bold text-rose-600 hover:text-rose-800 whitespace-nowrap">
                                    Remove role
                                </button>
                            </form>
                        </div>
                        @else
                        <div class="hidden lg:block" aria-hidden="true"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if(!empty($canEdit))
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{
            customRows: @js(collect($rows)->where('is_custom', true)->values()->map(fn ($r) => [
                'id' => $r['assignment_id'],
                'role_label' => $r['role_label'],
                'name' => $r['name'],
                'email' => $r['email'] ?? null,
                'phone' => $r['phone'] ?? null,
            ])->all() ?: [['id' => null, 'role_label' => '', 'name' => '', 'email' => null, 'phone' => null]]),
            employeeOptions: @js($employeeOptions->values()->all()),
            contacts: @js(($employeeOptions ?? collect())->mapWithKeys(fn ($o) => [strtolower(trim($o['value'] ?? '')) => ['email' => $o['email'] ?? null, 'phone' => $o['phone'] ?? null]])->all()),
            matchEmployeeValue(name) {
                const stored = (name || '').trim();
                if (!stored) return '';
                const exact = this.employeeOptions.find((option) => option.value === stored);
                if (exact) return exact.value;
                const normalize = (value) => {
                    const tokens = value
                        .replace(/([a-z])\.(?=[A-Za-z])/gi, '$1. ')
                        .split(/[\s,]+/)
                        .map((token) => token.replace(/[^a-z]/gi, '').toLowerCase())
                        .filter(Boolean);
                    if (!tokens.length) return { first: '', last: '' };
                    if (tokens.length === 1) return { first: tokens[0], last: '' };
                    const last = tokens[tokens.length - 1];
                    const first = tokens[0];
                    return { first, last };
                };
                const storedParts = normalize(stored);
                const fuzzy = this.employeeOptions.find((option) => {
                    const parts = normalize(option.value || '');
                    return storedParts.last && storedParts.last === parts.last
                        && storedParts.first && parts.first
                        && storedParts.first.startsWith(parts.first.slice(0, 1))
                        && parts.first.startsWith(storedParts.first.slice(0, 1));
                });
                return fuzzy ? fuzzy.value : stored;
            },
            isOrphanedName(name) {
                const stored = (name || '').trim();
                if (!stored) return false;
                return this.matchEmployeeValue(stored) === stored
                    && !this.employeeOptions.some((option) => option.value === stored);
            },
            contactFor(name) {
                const key = (name || '').trim().toLowerCase();
                return this.contacts[key] || { email: null, phone: null };
            },
            display(value) {
                return value && String(value).trim() !== '' ? value : '—';
            },
            onNameChange(row) {
                const contact = this.contactFor(row.name);
                row.email = contact.email;
                row.phone = contact.phone;
            }
        }">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-slate-900">Additional roles</h2>
                <button type="button"
                        @click="customRows.push({ id: null, role_label: '', name: '', email: null, phone: null })"
                        class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">
                    + Add role
                </button>
            </div>
            <template x-for="(row, index) in customRows" :key="index">
                <div class="grid gap-2 border-t border-slate-100 px-4 py-3 lg:grid-cols-12 lg:items-center">
                    <input type="hidden" :name="'custom_roles[' + index + '][id]'" :value="row.id">
                    <div class="lg:col-span-3">
                        <input type="text"
                               :name="'custom_roles[' + index + '][role_label]'"
                               x-model="row.role_label"
                               placeholder="Role title (e.g. Consultant Pharmacist)"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                    <div class="lg:col-span-3">
                        <select :name="'custom_roles[' + index + '][name]'"
                                x-model="row.name"
                                @change="onNameChange(row)"
                                class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">— Vacant —</option>
                            <template x-for="option in employeeOptions" :key="option.value">
                                <option :value="option.value"
                                        :selected="matchEmployeeValue(row.name) === option.value"
                                        x-text="option.label"></option>
                            </template>
                            <template x-if="isOrphanedName(row.name)">
                                <option :value="row.name" x-text="row.name + ' (not in roster)'" selected></option>
                            </template>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <p class="truncate text-sm text-slate-700" x-text="display(row.email || contactFor(row.name).email)"></p>
                    </div>
                    <div class="lg:col-span-2">
                        <p class="truncate text-sm text-slate-700" x-text="display(row.phone || contactFor(row.name).phone)"></p>
                    </div>
                    <div class="flex justify-end lg:col-span-2">
                        @if(!empty($canRemoveRoles) && empty($row['name']) && !empty($row['assignment_id']))
                        <form method="POST"
                              action="{{ route('admin.facility.leadership.destroy', ['facility' => $facility->getRouteKey(), 'assignment' => $row['assignment_id']]) }}"
                              onsubmit="return confirm('Remove this custom leadership role?');"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Remove</button>
                        </form>
                        @else
                        <button type="button"
                                @click="customRows.splice(index, 1)"
                                class="text-xs font-bold text-rose-600 hover:text-rose-800">Remove</button>
                        @endif
                    </div>
                </div>
            </template>
            <p class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-500">Custom roles are appended after standard roles on the dashboard.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit"
                    class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700">
                Save leadership roster
            </button>
            @if($facility)
            <a href="{{ route('member.facility.dashboard', ['facility' => $facility->getRouteKey()]) }}"
               class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">View dashboard</a>
            @endif
        </div>
    </form>
    @else
        @php
            $customRows = collect($rows)->where('is_custom', true)->values();
        @endphp
        @if($customRows->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-slate-900">Additional roles</h2>
            </div>
            <div class="hidden border-b border-slate-100 bg-slate-50 px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500 lg:grid lg:grid-cols-4 lg:gap-x-4">
                <span>Title</span>
                <span>Name</span>
                <span>Company email</span>
                <span>Phone</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($customRows as $row)
                <div class="grid grid-cols-1 gap-2 px-4 py-3 lg:grid-cols-4 lg:items-center lg:gap-x-4">
                    <p class="text-sm font-semibold text-slate-700">{{ $row['role_label'] ?: 'Custom role' }}</p>
                    <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 lg:border-0 lg:bg-transparent lg:px-0 lg:py-0">
                        {{ $row['name'] !== '' && $row['name'] !== null ? $row['name'] : '— Vacant —' }}
                    </p>
                    <p class="truncate text-sm text-slate-700">
                        @if(!empty($row['email']))
                        <a href="mailto:{{ $row['email'] }}" class="text-teal-700 hover:underline">{{ $row['email'] }}</a>
                        @else
                        —
                        @endif
                    </p>
                    <p class="truncate text-sm text-slate-700">
                        @if(!empty($row['phone']))
                        <a href="tel:{{ preg_replace('/\D+/', '', $row['phone']) }}" class="text-teal-700 hover:underline">{{ $row['phone'] }}</a>
                        @else
                        —
                        @endif
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif
    @endif
</section>
@endsection
