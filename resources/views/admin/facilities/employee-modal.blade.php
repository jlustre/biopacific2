<div id="employee-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
        <button type="button" onclick="closeEmployeeModal()"
            class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4" id="employee-modal-title">Employee Details</h2>
        <div id="employee-modal-content">
            <!-- Employee details will be loaded here -->
        </div>
    </div>
</div>
<script>
    function openEmployeeModal(empId) {
    fetch(`/admin/employees/${empId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('employee-modal-content').innerHTML = html;
            document.getElementById('employee-modal').classList.remove('hidden');
        });
}
function closeEmployeeModal() {
    document.getElementById('employee-modal').classList.add('hidden');
    document.getElementById('employee-modal-content').innerHTML = '';
}
</script>