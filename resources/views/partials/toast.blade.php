<div x-data="{
    toastOpen: false,
    toastMsg: '',
    init() {
        // Listen for toast events from other components
        window.addEventListener('show-toast', (event) => {
            this.showToast(event.detail.message);
        });
    },
    showToast(message) {
        this.toastMsg = message;
        this.toastOpen = true;
        setTimeout(() => this.toastOpen = false, 1800);
    }
}" x-cloak x-show="toastOpen" x-transition
  class="fixed bottom-5 right-5 z-50 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-sm px-4 py-3 rounded-xl shadow-lg">
  <span x-text="toastMsg"></span>
</div>