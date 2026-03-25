<div class="flex flex-wrap gap-2 mb-4">
    <input id="searchInput" type="text" placeholder="Search by facility, type, or filename..."
        class="border rounded px-2 py-1" />
    <select id="typeFilter" class="border rounded px-2 py-1">
        <option value="">All Types</option>
        <option value="pdf">PDF</option>
        <option value="docx">Word (.docx)</option>
    </select>
    <select id="facilityFilter" class="border rounded px-2 py-1">
        <option value="">All Facilities</option>
        @foreach($templates->pluck('facility.name')->unique()->filter() as $facilityName)
        <option value="{{ $facilityName }}">{{ $facilityName }}</option>
        @endforeach
    </select>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const facilityFilter = document.getElementById('facilityFilter');
        const table = document.querySelector('table');
        const rows = Array.from(table.querySelectorAll('tbody tr'));

        function filterTable() {
            const search = searchInput.value.toLowerCase();
            const type = typeFilter.value;
            const facility = facilityFilter.value;
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const facilityText = cells[0].textContent.toLowerCase();
                const typeText = cells[1].textContent.toLowerCase();
                const fileText = cells[2].textContent.toLowerCase();
                let show = true;
                if (search && !(facilityText.includes(search) || typeText.includes(search) || fileText.includes(search))) {
                    show = false;
                }
                if (type && typeText !== type) {
                    show = false;
                }
                if (facility && facilityText !== facility.toLowerCase()) {
                    show = false;
                }
                row.style.display = show ? '' : 'none';
            });
        }
        searchInput.addEventListener('input', filterTable);
        typeFilter.addEventListener('change', filterTable);
        facilityFilter.addEventListener('change', filterTable);
    });
</script>