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


<section id="book"
    class="relative py-8 sm:py-12 bg-gradient-to-br from-slate-50 via-white to-blue-50 min-h-screen flex items-center justify-center">
    <div class="absolute inset-0 pointer-events-none -z-10">
        <div class="absolute -top-24 -left-24 h-60 w-60 rounded-full blur-2xl opacity-10"
            style="background: {{ $primary }}"></div>
        <div class="absolute -bottom-28 -right-24 h-72 w-72 rounded-full blur-2xl opacity-10"
            style="background: {{ $accent }}"></div>
    </div>
    <div class="w-full max-w-4xl mx-auto px-2 sm:px-4">
        <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-stretch">
            <!-- Calendar & Time Slots -->
            <div
                class="md:w-5/12 w-full bg-white/90 rounded-2xl shadow-md border border-slate-100 p-4 flex flex-col justify-between">
                <h3 class="text-lg font-bold text-slate-900 mb-2">Pick a Date & Time</h3>
                <div class="mb-2">
                    <div id="calDays" class="mb-3"></div>
                    <div id="calMonthLabel" class="text-xs text-slate-500 mb-2"></div>
                </div>
                <div class="mb-2">
                    <div id="timeSlots" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach(['9:00 AM','10:30 AM','1:00 PM','2:30 PM','4:00 PM'] as $slot)
                        <label class="group relative">
                            <input type="radio" name="preferred_time" value="{{ $slot }}" class="peer sr-only">
                            <span
                                class="block rounded-lg border bg-white px-2.5 py-1.5 text-xs text-slate-700 peer-checked:text-white peer-checked:shadow transition select-none cursor-pointer border-slate-200 peer-checked:bg-blue-500 peer-checked:border-blue-500">
                                {{ $slot }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div
                    class="rounded-xl bg-slate-50 px-2 py-1.5 ring-1 ring-slate-200 text-xs text-slate-700 text-center mt-2">
                    Free on-site parking</div>
            </div>
            <!-- Compact Form -->
            <div
                class="md:w-7/12 w-full bg-white/95 rounded-2xl shadow-md border border-slate-100 p-4 flex flex-col justify-between">
                <h2 class="text-2xl font-bold text-blue-900 mb-1">Book a Tour</h2>
                <p class="text-slate-600 text-sm mb-3">Experience our facility in person. Fill out the form and our team
                    will reach out to you soon.</p>
                <div class="mb-3">
                    <div class="rounded-xl bg-amber-50 p-2 ring-1 ring-amber-200 text-xs text-amber-800 text-center">
                        ⚠ Please avoid sharing personal medical details (PHI) in this form. We’ll discuss specifics
                        privately.
                    </div>
                </div>
                <form method="POST" action="{{ route('tours.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="name" class="block text-slate-700 text-xs font-medium mb-1">Full Name</label>
                            <input type="text" id="name" name="name" required
                                class="w-full rounded-md border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                        </div>
                        <div>
                            <label for="email" class="block text-slate-700 text-xs font-medium mb-1">Email</label>
                            <input type="email" id="email" name="email" required
                                class="w-full rounded-md border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="phone" class="block text-slate-700 text-xs font-medium mb-1">Phone</label>
                            <input type="text" id="phone" name="phone"
                                class="w-full rounded-md border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                        </div>
                        <div>
                            <label for="date" class="block text-slate-700 text-xs font-medium mb-1">Preferred
                                Date</label>
                            <input type="date" id="date" name="date"
                                class="w-full rounded-md border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90">
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-slate-700 text-xs font-medium mb-1">Message</label>
                        <textarea id="message" name="message" rows="2"
                            class="w-full rounded-md border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white/90"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="phi_ack" name="phi_ack" required class="accent-blue-600">
                        <label for="phi_ack" class="text-xs text-slate-700">I understand not to include Protected Health
                            Information (PHI).</label>
                    </div>
                    <div class="text-xs text-slate-600 mb-1">
                        By submitting, you agree to our <a href="/npp" target="_blank"
                            class="underline hover:text-blue-900">Notice of Privacy Practices</a>.
                    </div>
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold py-2.5 rounded-lg shadow transition text-sm">Book
                        Tour</button>
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