<section class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between text-sm">
      <div class="flex items-center gap-3">
          <button @click="toggleLargeText" class="px-3 py-1 rounded-lg border border-slate-300 dark:border-slate-600 hover:bg-white dark:hover:bg-slate-700">A<span class="text-xs align-super">+</span> Text</button>
          <button @click="toggleHighContrast" class="px-3 py-1 rounded-lg border border-slate-300 dark:border-slate-600 hover:bg-white dark:hover:bg-slate-700">High Contrast</button>
      </div>
      <div class="flex items-center gap-2">
      <button :class="lang==='en' ? 'bg-primary text-white' : 'border border-slate-300 dark:border-slate-600'" @click="setLang('en')" class="px-3 py-1 rounded-lg">EN</button>
      <button :class="lang==='es' ? 'bg-primary text-white' : 'border border-slate-300 dark:border-slate-600'" @click="setLang('es')" class="px-3 py-1 rounded-lg">ES</button>
      </div>
  </div>
</section>
