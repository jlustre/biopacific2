
<div id="importModal" class="fixed inset-0 flex items-center justify-center bg-gradient-to-br from-blue-100 via-indigo-100 to-purple-100 bg-opacity-90 z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl p-0 relative border border-indigo-200" style="max-height:92vh; overflow-y:auto;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-8 pt-8 pb-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 text-white text-2xl shadow">
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 17l4 4 4-4m0 0V3m0 14l-4-4-4 4'/></svg>
                </span>
                <div>
                    <h2 class="text-2xl font-semibold text-indigo-800 mb-0">Import Facility Data</h2>
                    <div class="text-base font-medium text-indigo-500">{{ $facility->name }}</div>
                </div>
            </div>
            <button type="button" class="text-gray-400 hover:text-red-500 text-3xl font-semibold focus:outline-none" onclick="document.getElementById('importModal').classList.add('hidden')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Upload Section -->
        <form id="excelUploadForm" method="POST" action="{{ route('admin.facility.files.import', ['facility' => $facility->id]) }}" enctype="multipart/form-data" onsubmit="showMappingStep(event)" class="px-8 pt-6 pb-2">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2" for="importFile">
                    <span class="inline-flex items-center gap-1"><svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 text-indigo-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3'/></svg> Excel File</span>
                </label>
                <input type="file" name="file" id="importFile" accept=".xlsx,.xls,.csv" required class="border-2 border-indigo-200 rounded-lg w-full p-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none transition">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-2 py-1 rounded-lg shadow transition border border-teal-700">Next</button>
            </div>
        </form>

        <!-- Mapping UI (hidden by default, shown after upload) -->
        <div class="px-8 pb-8">
            @include('admin.facilities.partials.import-mapping')
        </div>
        @include('admin.facilities.partials.import-mapping-scripts')
    </div>
</div>