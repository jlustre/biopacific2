<div id="screen-size-indicator" class="fixed bottom-0 right-0 bg-red-800 text-white text-xs p-2 rounded-tl z-1000">
    <div id="screen-pixel-width" class="text-xs font-bold">0px</div>
    <span id="screen-size-label" class="text-center">xs</span>
</div>

<script>
    function updateScreenSizeLabel() {
        const width = window.innerWidth;
        const label = document.getElementById('screen-size-label');
        const pixelWidth = document.getElementById('screen-pixel-width');

        pixelWidth.textContent = `${width}`;

        if (width < 640) {
            label.textContent = 'xs';
        } else if (width < 768) {
            label.textContent = 'sm';
        } else if (width < 1024) {
            label.textContent = 'md';
        } else if (width < 1280) {
            label.textContent = 'lg';
        } else {
            label.textContent = 'xl';
        }
    }

    window.addEventListener('resize', updateScreenSizeLabel);
    document.addEventListener('DOMContentLoaded', updateScreenSizeLabel);
</script>