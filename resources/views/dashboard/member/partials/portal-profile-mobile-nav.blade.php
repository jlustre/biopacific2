<nav class="fixed bottom-0 left-0 right-0 z-30 border-t border-slate-200 bg-white px-3 py-2 shadow-2xl lg:hidden">
  <div class="grid grid-cols-5 text-center text-xs font-semibold text-slate-500">
    <button type="button" @click="activeTab='overview'" :class="activeTab==='overview' && 'text-brand-700'" class="rounded-xl p-2">👤<span class="block">Profile</span></button>
    <button type="button" @click="activeTab='personal'" :class="activeTab==='personal' && 'text-brand-700'" class="rounded-xl p-2">📝<span class="block">Info</span></button>
    <button type="button" @click="activeTab='certifications'" :class="activeTab==='certifications' && 'text-brand-700'" class="rounded-xl p-2">🏅<span class="block">Certs</span></button>
    <button type="button" @click="activeTab='documents'" :class="activeTab==='documents' && 'text-brand-700'" class="rounded-xl p-2">📄<span class="block">Docs</span></button>
    <button type="button" @click="activeTab='security'" :class="activeTab==='security' && 'text-brand-700'" class="rounded-xl p-2">🔒<span class="block">Security</span></button>
  </div>
</nav>
