@if($canGenerateRegistrationCodes ?? false)
    @if($hasPortalUser ?? false)
        <span class="text-emerald-600" title="Registered portal user">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </span>
    @elseif(!empty($generateUrl))
        <form method="POST" action="{{ $generateUrl }}" class="inline">
            @csrf
            @if(!empty($facilityFilterId))
                <input type="hidden" name="facility" value="{{ $facilityFilterId }}">
            @endif
            <button type="submit"
                class="{{ !empty($pendingRegistrationCode) ? 'text-amber-600 hover:text-amber-800' : 'text-teal-600 hover:text-teal-800' }} transition"
                title="{{ !empty($pendingRegistrationCode) ? 'Resend registration code (' . $pendingRegistrationCode->code . ')' : 'Generate registration code and email invite' }}">
                @if(!empty($pendingRegistrationCode))
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5 align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                @endif
            </button>
        </form>
    @endif
@endif
