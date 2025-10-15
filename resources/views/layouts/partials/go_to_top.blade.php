<button x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = window.scrollY > 200 })"
    x-show="show" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
    class="fixed bottom-6 right-6 z-50 bg-teal-500 text-white rounded-full shadow-lg p-3 hover:bg-teal-600 transition-all"
    style="display: none;">
    <i class="fas fa-arrow-up"></i>
</button>