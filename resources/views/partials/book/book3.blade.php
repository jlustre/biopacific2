{{-- ==========================================
BOOK A TOUR — Split Calendar Layout Variant
- Left: selectable calendar (next 21 days) + time slots
- Right: compact contact form
- Mobile: sticky CTA + stacked sections
========================================== --}}
@php
$primary = $facility['primary_color'] ?? '#0EA5E9';
$secondary = $facility['secondary_color'] ?? '#1E293B';
$accent = $facility['accent_color'] ?? '#F59E0B';
@endphp

<section id="book" class="relative isolate overflow-hidden py-16 sm:py-20">
    {{-- Ambient brand background --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-15"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-80 w-80 rounded-full blur-3xl opacity-10"
            style="background: {{ $accent }}"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-slate-50/70"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                style="color: {{ $primary }}; border-color: {{ $primary }};">
                <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
                Quick & Easy Scheduling
            </span>
            <h2 class="mt-4 text-3xl md:text-4xl font-extrabold text-slate-900">
                Book a Tour at {{ $facility['name'] ?? 'Our Community' }}
            </h2>
            <p class="mt-2 text-slate-600 md:text-lg">
                Choose a date and time that works for you—then tell us how we can tailor your visit.
            </p>
        </div>

        {{-- Content Grid --}}
        <div class="mt-10 grid gap-8 lg:grid-cols-[1.05fr,0.95fr] items-start">
            {{-- LEFT: Calendar & Time --}}
            <div class="space-y-6">
                <div class="rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden relative">
                    {{-- Calendar Header --}}
                    <div class="flex items-center justify-between gap-3 p-6 border-b border-slate-200">
                        <div>
                            <div class="text-sm text-slate-600">Pick a date</div>
                            <div id="calMonthLabel" class="text-lg font-semibold text-slate-900">—</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="calPrev"
                                class="h-9 w-9 inline-flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200"
                                aria-label="Previous week">‹</button>
                            <button id="calNext"
                                class="h-9 w-9 inline-flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200"
                                aria-label="Next week">›</button>
                        </div>
                    </div>

                    {{-- Calendar Grid (3 weeks rolling) --}}
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-7 gap-2 text-center text-xs text-slate-500 uppercase tracking-wide">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>

                        <div id="calDays" class="mt-2 grid grid-cols-7 gap-2">
                            {{-- JS will populate next 21 days as buttons --}}
                        </div>

                        {{-- Quick help --}}
                        <div class="mt-4 grid gap-3 sm:grid-cols-3 text-xs text-slate-600">
                            <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Tours last 20–30 minutes
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Private tours available
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2 ring-1 ring-slate-200">Free on-site parking
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Time Slots --}}
                <div class="rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden relative">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Pick a time</h3>
                        <p class="text-sm text-slate-600">We’ll confirm or suggest the closest available slot.</p>
                    </div>

                    <div class="p-6">
                        <div id="timeSlots" class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            @foreach(['9:00 AM','10:30 AM','1:00 PM','2:30 PM','4:00 PM'] as $slot)
                            <label class="group relative">
                                <input type="radio" name="preferred_time" value="{{ $slot }}" class="peer sr-only">
                                <span class="block rounded-xl border bg-white px-3 py-2 text-sm text-slate-700
                               peer-checked:text-white peer-checked:shadow transition select-none cursor-pointer"
                                    style="border-color:#e5e7eb;">
                                    {{ $slot }}
                                </span>
                                <style>
                                    .peer:checked+span {
                                        background: {
                                                {
                                                $primary
                                            }
                                        }

                                        ;
                                        border-color: transparent;
                                    }
                                </style>
                            </label>
                            @endforeach
                        </div>

                        {{-- Mobile sticky CTA mirrors summary --}}
                        <div class="mt-6 lg:hidden">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div id="mDate" class="font-semibold">Date: —</div>
                                        <div id="mTime" class="text-slate-600 text-xs">Time: —</div>
                                    </div>
                                    <a href="#tourFormWrap"
                                        class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-white"
                                        style="background: {{ $primary }}">Continue</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trust badges --}}
                <div class="grid gap-3 sm:grid-cols-3 relative">
                    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 text-center">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Location</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ $facility['city'] ?? '—' }}{{ isset($facility['state']) ? ', '.$facility['state'] : '' }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 text-center">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Tours</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $facility['hours'] ?? '9AM–7PM' }}</div>
                    </div>
                    <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 text-center">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Phone</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ isset($facility['phone']) ? preg_replace('/(\d{3})(\d{3})(\d{4})/','($1)
                            $2-$3',$facility['phone']) : '—' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Compact Form --}}
            <div id="tourFormWrap" class="rounded-3xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50/60">
                    <h3 class="text-lg font-semibold text-slate-900">Tell us about you</h3>
                    <p class="text-sm text-slate-600">We’ll confirm within one business day.</p>
                </div>

                <form method="POST" action="{{ route('tours.store') }}" id="tourForm" class="p-6 sm:p-8" novalidate>
                    @csrf
                    <input type="hidden" name="type" value="in_person">
                    <input type="hidden" name="preferred_date" id="preferred_date">
                    <input type="hidden" name="preferred_time_hidden" id="preferred_time_hidden">

                    <div class="mb-4 p-3 rounded-lg bg-amber-100 text-amber-800 border border-amber-200">
                        <strong>Warning:</strong> Do not include any Protected Health Information (PHI) in this request
                        form.
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="full_name" class="text-sm font-medium text-slate-700">Full name *</label>
                            <input id="full_name" name="full_name" required placeholder="Jane Doe"
                                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-slate-400 focus:ring-0 px-3 py-2" />
                        </div>
                        <div>
                            <label for="relationship" class="text-sm font-medium text-slate-700">Relationship</label>
                            <select id="relationship" name="relationship"
                                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-slate-400 focus:ring-0 px-3 py-2">
                                <option value="">Select…</option>
                                <option>Self</option>
                                <option>Spouse</option>
                                <option>Parent</option>
                                <option>Adult child</option>
                                <option>Relative</option>
                                <option>Friend</option>
                                <option>Care manager</option>
                            </select>
                        </div>
                        <div>
                            <label for="phone" class="text-sm font-medium text-slate-700">Phone *</label>
                            <input id="phone" name="phone" type="tel" required placeholder="(555) 555-1234"
                                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-slate-400 focus:ring-0 px-3 py-2" />
                        </div>
                        <div>
                            <label for="email" class="text-sm font-medium text-slate-700">Email *</label>
                            <input id="email" name="email" type="email" required placeholder="you@example.com"
                                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-slate-400 focus:ring-0 px-3 py-2" />
                        </div>
                    </div>

                    {{-- Guests & Interests --}}
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="guests" class="text-sm font-medium text-slate-700">Guests</label>
                            <input id="guests" name="guests" type="number" min="1" max="6" value="1"
                                class="mt-1 block w-full rounded-md border border-gray-300 focus:border-slate-400 focus:ring-0 px-3 py-2" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Areas of interest</label>
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                @foreach(['Skilled Nursing','Rehabilitation','Memory Care','Long-term Care'] as $opt)
                                <label
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                                    <input type="checkbox" name="interests[]" value="{{ $opt }}"
                                        class="rounded border border-gray-300 text-sky-600 focus:ring-sky-500">
                                    <span>{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="message" class="text-sm font-medium text-slate-700">Notes (optional)</label>
                        <textarea id="message" name="message" rows="4"
                            class="mt-1 block w-full rounded-md border border-gray-300 focus:border-slate-400 focus:ring-0 px-3 py-2"
                            placeholder="Accessibility needs, questions, preferences…"></textarea>
                    </div>

                    <div class="mt-5 space-y-2 text-xs text-slate-600">
                        <label class="inline-flex items-start gap-2">
                            <input type="checkbox" name="consent" required
                                class="mt-0.5 rounded border border-gray-300 text-sky-600 focus:ring-sky-500">
                            <span>I agree to be contacted about this request. Please do not include sensitive medical
                                information.</span>
                        </label>
                        <p>See our <a href="{{ $facility['npp_url'] ?? url('/privacy-practices') }}" class="underline"
                                style="color: {{ $primary }}">Notice of Privacy Practices</a>.</p>
                    </div>

                    {{-- Honeypot --}}
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">

                    <div class="mt-6 flex flex-col sm:flex-row gap-3 items-center">
                        <button type="submit"
                            class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl px-6 py-3 font-semibold text-white shadow-lg hover:shadow-xl hover:bg-green-600 transition"
                            style="background: {{ $primary }}">Request Tour</button>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="tel:{{ $facility['phone'] ?? '' }}"
                                class="inline-flex items-center justify-center rounded-2xl px-6 py-3 font-semibold ring-2 transition bg-white text-slate-900 hover:bg-slate-200"
                                style="border-color: {{ $primary }}">Call Us</a>
                            <span class="text-sm text-slate-700 font-medium">{{ isset($facility['phone']) ?
                                preg_replace('/(\d{3})(\d{3})(\d{4})/','($1) $2-$3',$facility['phone']) : '' }}</span>
                        </div>
                    </div>

                    {{-- Inline Summary --}}
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-slate-700">
                        <div class="flex flex-wrap items-center gap-4">
                            <div><span class="text-slate-500">Date:</span> <span id="sumDate">—</span></div>
                            <div><span class="text-slate-500">Time:</span> <span id="sumTime">—</span></div>
                            <div><span class="text-slate-500">Guests:</span> <span id="sumGuests">1</span></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    // Colors
  const BRAND_PRIMARY = "{{ $primary }}";


    // Generate next 21 days in a rolling 7x3 grid, aligned to correct weekday
    const calDays = document.getElementById('calDays');
    const calMonthLabel = document.getElementById('calMonthLabel');
    const preferredDateInput = document.getElementById('preferred_date');
    const timeInputs = document.querySelectorAll('input[name="preferred_time"]');
    const timeHidden = document.getElementById('preferred_time_hidden');

    const mDate = document.getElementById('mDate');
    const mTime = document.getElementById('mTime');
    const sumDate = document.getElementById('sumDate');
    const sumTime = document.getElementById('sumTime');
    const sumGuests = document.getElementById('sumGuests');

    let startOffset = 0;   // weeks offset for prev/next
    let selectedDate = null;

    function fmtDate(d){
        return d.toISOString().slice(0,10); // yyyy-mm-dd
    }
    function humanDate(d){
        const opts = { weekday:'short', month:'short', day:'numeric' };
        return d.toLocaleDateString(undefined, opts);
    }
    function updateMonthLabel(d){
        const opts = { month:'long', year:'numeric' };
        calMonthLabel.textContent = d.toLocaleDateString(undefined, opts);
    }

    function renderCalendar(){
        if (!calDays) return;
        calDays.innerHTML = '';
        const today = new Date();
        today.setHours(0,0,0,0);

        // Move window by startOffset weeks
        const start = new Date(today);
        start.setDate(start.getDate() + startOffset * 7);

        // Find the weekday of the start date
        const startWeekday = start.getDay();
        // Back up to the previous Sunday
        start.setDate(start.getDate() - startWeekday);

        const previewDay = new Date(start);
        updateMonthLabel(previewDay);

        for (let i=0; i<21; i++){
            const d = new Date(start);
            d.setDate(start.getDate() + i);

            const isPast = d < today;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cal-day group relative w-full aspect-square rounded-2xl border text-sm transition';
            btn.style.borderColor = '#e5e7eb';
            btn.setAttribute('aria-label', 'Select ' + humanDate(d));
            btn.dataset.date = fmtDate(d);

            const weekday = d.getDay();
            if (weekday === 0 || weekday === 6) {
                // weekends subtly muted, still selectable
                btn.classList.add('bg-slate-50');
            } else {
                btn.classList.add('bg-white');
            }

            if (isPast) {
                btn.disabled = true;
                btn.classList.add('opacity-40','cursor-not-allowed');
            } else {
                btn.addEventListener('click', () => selectDate(d, btn));
            }

            btn.innerHTML = `
                <div class="flex h-full items-center justify-center font-semibold text-slate-900">${d.getDate()}</div>
            `;
            calDays.appendChild(btn);
        }

        // Reselect previous date if still visible
        if (selectedDate) {
            document.querySelectorAll('.cal-day').forEach(el=>{
                if (el.dataset.date === fmtDate(selectedDate)) {
                    highlightDateButton(el);
                }
            });
        }
    }

  function clearDateHighlights(){
    document.querySelectorAll('.cal-day').forEach(el=>{
      el.style.background = '';
      el.style.borderColor = '#e5e7eb';
      el.style.color = '';
      el.classList.remove('ring-2');
    });
  }

  function highlightDateButton(btn){
    clearDateHighlights();
    btn.classList.add('ring-2');
    btn.style.borderColor = 'transparent';
    btn.style.background = BRAND_PRIMARY;
    btn.style.color = '#fff';
  }

  function selectDate(d, btn){
    selectedDate = d;
    preferredDateInput.value = fmtDate(d);
    highlightDateButton(btn);
    // mobile + summary mirrors
    const label = humanDate(d);
    if (mDate) mDate.textContent = 'Date: ' + label;
    if (sumDate) sumDate.textContent = label;
  }

  // Time selection mirror
  timeInputs.forEach(r=>{
    r.addEventListener('change', ()=>{
      timeHidden.value = r.value;
      if (mTime) mTime.textContent = 'Time: ' + r.value;
      if (sumTime) sumTime.textContent = r.value;
    });
  });

  // Guests mirror
  document.getElementById('guests')?.addEventListener('input', (e)=>{
    if (sumGuests) sumGuests.textContent = e.target.value || '1';
  });

  // Prev/Next week
  document.getElementById('calPrev')?.addEventListener('click', ()=>{
    startOffset = Math.max(0, startOffset - 1); // do not allow navigating before today
    renderCalendar();
  });
  document.getElementById('calNext')?.addEventListener('click', ()=>{
    startOffset += 1;
    renderCalendar();
  });

  // Init
  document.addEventListener('DOMContentLoaded', ()=>{
    renderCalendar();
    // Auto-select today by default
    const todayBtn = document.querySelector('.cal-day:not([disabled])');
    if (todayBtn) {
      const d = new Date(todayBtn.dataset.date);
      selectDate(d, todayBtn);
    }
  });
</script>