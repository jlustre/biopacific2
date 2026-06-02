@php
    use App\Models\MemberEmergencyContact;
    use Illuminate\Support\Js;
    $contacts = $emergencyContacts ?? collect();
    $relationshipOptions = MemberEmergencyContact::relationshipOptions();
@endphp

<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-black text-slate-900">Emergency Contacts</h2>
            <p class="mt-0.5 text-sm text-slate-500">People we can reach if you are unavailable.</p>
        </div>
        <button type="button"
                @click="openEmergencyAdd()"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700">
            <i class="fa-solid fa-plus text-xs"></i> Add Contact
        </button>
    </div>

    @if($contacts->isEmpty())
    <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
        <p class="text-sm font-medium text-slate-600">No emergency contacts yet.</p>
        <p class="mt-1 text-xs text-slate-500">Add at least one person we can call in an emergency.</p>
        <button type="button" @click="openEmergencyAdd()"
                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">
            <i class="fa-solid fa-plus text-xs"></i> Add your first contact
        </button>
    </div>
    @else
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @foreach($contacts as $contact)
        <article class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 shadow-sm {{ $contact->is_primary ? 'border-l-4 border-l-teal-600' : '' }}">
            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-sm font-black {{ $contact->is_primary ? 'bg-teal-100 text-teal-800' : 'bg-slate-200 text-slate-700' }}">
                    {{ $contact->initials }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">{{ $contact->full_name }}</h3>
                        @if($contact->is_primary)
                        <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-teal-800">Primary</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600">{{ $contact->relationship }}</p>
                    <ul class="mt-3 space-y-1.5 text-sm text-slate-700">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-phone w-4 text-center text-slate-400 text-xs"></i>
                            {{ $contact->formatted_phone }}
                        </li>
                        @if($contact->email)
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope w-4 text-center text-slate-400 text-xs"></i>
                            <span class="truncate">{{ $contact->email }}</span>
                        </li>
                        @endif
                        @if($contact->formatted_address)
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot mt-0.5 w-4 text-center text-slate-400 text-xs"></i>
                            <span>{{ $contact->formatted_address }}</span>
                        </li>
                        @endif
                    </ul>
                    <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold">
                        <button type="button"
                                @click="openEmergencyEdit({{ Js::from($contact->only([
                                    'id', 'first_name', 'last_name', 'relationship', 'phone', 'email',
                                    'address1', 'address2', 'city', 'state', 'zip', 'is_primary',
                                ])) }})"
                                class="text-teal-700 hover:text-teal-900">Edit</button>
                        <form method="POST" action="{{ route('settings.profile.emergency-contacts.destroy', $contact) }}"
                              onsubmit="return confirm('Remove this emergency contact?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-800">Delete</button>
                        </form>
                        @unless($contact->is_primary)
                        <form method="POST" action="{{ route('settings.profile.emergency-contacts.primary', $contact) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-teal-700 hover:text-teal-900">Set as Primary</button>
                        </form>
                        @endunless
                    </div>
                </div>
            </div>
        </article>
        @endforeach
    </div>
    @endif
</div>

{{-- Modal --}}
<div x-show="emergencyModalOpen" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
     @keydown.escape.window="closeEmergencyModal()">
    <div class="absolute inset-0 bg-slate-900/50" @click="closeEmergencyModal()"></div>
    <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
         @click.stop>
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg font-black text-slate-900" x-text="emergencyEditingId ? 'Edit contact' : 'Add contact'"></h3>
            <button type="button" @click="closeEmergencyModal()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">✕</button>
        </div>

        <form x-ref="emergencyForm"
              method="POST"
              :action="emergencyEditingId
                  ? '{{ url('settings/profile/emergency-contacts') }}/' + emergencyEditingId
                  : '{{ route('settings.profile.emergency-contacts.store') }}'"
              class="mt-5 space-y-4">
            @csrf
            <template x-if="emergencyEditingId">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-bold uppercase text-slate-500">First name</span>
                    <input type="text" name="first_name" required maxlength="100" x-ref="emFirstName"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase text-slate-500">Last name</span>
                    <input type="text" name="last_name" required maxlength="100" x-ref="emLastName"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
                </label>
            </div>
            <label class="block">
                <span class="text-xs font-bold uppercase text-slate-500">Relationship</span>
                <select name="relationship" required x-ref="emRelationship"
                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15">
                    <option value="">Select relationship</option>
                    @foreach($relationshipOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-bold uppercase text-slate-500">Phone</span>
                <input type="text" name="phone" required maxlength="32" x-ref="emPhone" placeholder="(555) 123-4567"
                       class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
            </label>
            <label class="block">
                <span class="text-xs font-bold uppercase text-slate-500">Email</span>
                <input type="email" name="email" maxlength="255" x-ref="emEmail"
                       class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
            </label>
            <label class="block">
                <span class="text-xs font-bold uppercase text-slate-500">Street address</span>
                <input type="text" name="address1" maxlength="255" x-ref="emAddress1"
                       class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
            </label>
            <label class="block">
                <span class="text-xs font-bold uppercase text-slate-500">Address line 2</span>
                <input type="text" name="address2" maxlength="255" x-ref="emAddress2"
                       class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
            </label>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="block sm:col-span-1">
                    <span class="text-xs font-bold uppercase text-slate-500">City</span>
                    <input type="text" name="city" maxlength="100" x-ref="emCity"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase text-slate-500">State</span>
                    <input type="text" name="state" maxlength="32" x-ref="emState"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase text-slate-500">ZIP</span>
                    <input type="text" name="zip" maxlength="20" x-ref="emZip"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-teal-400 focus:ring-4 focus:ring-teal-500/15"/>
                </label>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_primary" value="1" x-ref="emIsPrimary" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"/>
                <span class="font-medium text-slate-700">Set as primary emergency contact</span>
            </label>

            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-700">Save contact</button>
                <button type="button" @click="closeEmergencyModal()" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
            </div>
        </form>
    </div>
</div>
