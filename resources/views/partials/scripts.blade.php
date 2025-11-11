<script>
  function siteUI(){
      return {
        mobileOpen:false,
        openRates:false,
        openApply:false,
        applyRole:'',
        largeText: JSON.parse(localStorage.getItem('largeText') || 'false'),
        highContrast: JSON.parse(localStorage.getItem('highContrast') || 'false'),
        darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'),
        lang: localStorage.getItem('lang') || 'en',
        nav: [
          {label:'About', href:'#about'},
          {label:'Services', href:'#services'},
          {label:'Rooms & Rates', href:'#rooms'},
          {label:'Careers', href:'#careers'},
          {label:'News & Events', href:'#news'},
          {label:'Testimonials', href:'#testimonials'},
          {label:'Gallery', href:'#gallery'},
          {label:'Contact', href:'#contact'},
          {label:'FAQs', href:'#faqs'},
          {label:'Resources', href:'#resources'},
        ],
        init() {
          // siteUI initialization - go to top functionality moved to gototop.blade.php
        },
        toggleLargeText(){ this.largeText=!this.largeText; localStorage.setItem('largeText', this.largeText) },
        toggleHighContrast(){ this.highContrast=!this.highContrast; localStorage.setItem('highContrast', this.highContrast) },
        toggleDarkMode(){ this.darkMode=!this.darkMode; localStorage.setItem('darkMode', this.darkMode) },
        setLang(v){ this.lang=v; localStorage.setItem('lang', v) },
        toast(msg){ 
            // Dispatch event to the toast component
            window.dispatchEvent(new CustomEvent('show-toast', { 
                detail: { message: msg } 
            }));
        }
      }
    }


      document.addEventListener("livewire:navigated", () => {
    if (window.Alpine && Alpine.initTree) {
      Alpine.initTree(document.body);
    }
  });
  document.addEventListener("livewire:load", () => {
    if (window.Alpine && Alpine.initTree) {
      Alpine.initTree(document.body);
    }
  });

    document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"], a[href*="#"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var href = link.getAttribute('href');
            if (href && href.startsWith('#')) {
                var target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
});
</script>