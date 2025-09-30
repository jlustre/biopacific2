// Custom color scheme dropdown for facility edit
// Usage: call initColorSchemeDropdown() after DOMContentLoaded

function initColorSchemeDropdown() {
    var select = document.getElementById('color_scheme_id');
    if (!select) return;

    // Hide the original select
    select.style.display = 'none';

    // Build custom dropdown
    var wrapper = document.createElement('div');
    wrapper.className = 'relative w-full';
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'w-full flex items-center justify-between rounded border border-gray-400 bg-yellow-50 px-4 py-2 shadow-sm focus:border-primary focus:ring-primary';
    button.id = 'colorSchemeDropdownBtn';
    var selectedId = select.value;
    var selectedOption = select.querySelector('option[value="' + selectedId + '"]');
    button.innerHTML = renderOption(selectedOption);

    var list = document.createElement('div');
    list.className = 'absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded shadow-lg hidden custom-color-dropdown-list';
    list.id = 'colorSchemeDropdownList';

    Array.from(select.options).forEach(function(opt) {
        var item = document.createElement('div');
        item.className = 'flex items-center px-4 py-2 cursor-pointer hover:bg-yellow-100';
        item.innerHTML = renderOption(opt);
        item.dataset.value = opt.value;
        item.onclick = function() {
            select.value = opt.value;
            button.innerHTML = renderOption(opt);
            list.classList.add('hidden');
            select.dispatchEvent(new Event('change'));
        };
        list.appendChild(item);
    });

    button.onclick = function(e) {
        e.preventDefault();
        list.classList.toggle('hidden');
    };

    wrapper.appendChild(button);
    wrapper.appendChild(list);
    select.parentNode.insertBefore(wrapper, select);

    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            list.classList.add('hidden');
        }
    });
}

function renderOption(opt) {
    var name = opt.textContent || opt.innerText;
    var primary = opt.getAttribute('data-primary') || '#007bff';
    var secondary = opt.getAttribute('data-secondary') || '#6c757d';
    var accent = opt.getAttribute('data-accent') || '#28a745';
    return `<span class="mr-2 text-sm font-medium">${name}</span>
        <span class="inline-block w-5 h-5 rounded-full border" style="background:${primary}" title="Primary"></span>
        <span class="inline-block w-5 h-5 rounded-full border ml-1" style="background:${secondary}" title="Secondary"></span>
        <span class="inline-block w-5 h-5 rounded-full border ml-1" style="background:${accent}" title="Accent"></span>`;
}

// To use: add <script src="/js/color-scheme-dropdown.js"></script> and call initColorSchemeDropdown() after DOMContentLoaded.
