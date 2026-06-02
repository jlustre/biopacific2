<nav class="fixed bottom-0 left-0 right-0 z-30 border-t border-slate-200 bg-white/95 px-2 py-2 shadow-2xl backdrop-blur lg:hidden">
  <div class="grid grid-cols-5 text-center text-[10px] font-semibold text-slate-500">
    <button type="button" @click="section='overview'" :class="section==='overview' && 'text-teal-700'" class="rounded-xl p-2">
      <i class="fa-solid fa-house mb-0.5 block text-base"></i>Overview
    </button>
    <button type="button" @click="section='account'; editing=true" :class="section==='account' && 'text-teal-700'" class="rounded-xl p-2">
      <i class="fa-solid fa-user mb-0.5 block text-base"></i>Account
    </button>
    <button type="button" @click="section='contact'" :class="section==='contact' && 'text-teal-700'" class="rounded-xl p-2">
      <i class="fa-solid fa-address-book mb-0.5 block text-base"></i>Contact
    </button>
    <button type="button" @click="section='emergency-contacts'" :class="section==='emergency-contacts' && 'text-teal-700'" class="rounded-xl p-2">
      <i class="fa-solid fa-truck-medical mb-0.5 block text-base"></i>Emergency
    </button>
    <button type="button" @click="section='security'" :class="section==='security' && 'text-teal-700'" class="rounded-xl p-2">
      <i class="fa-solid fa-shield-halved mb-0.5 block text-base"></i>Security
    </button>
  </div>
</nav>
