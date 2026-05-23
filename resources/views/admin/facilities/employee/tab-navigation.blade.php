<div class="flex space-x-2 my-4 border-b border-teal-500">

    <button type="button" data-employee-tab-btn="personal" @click="setTab('personal')"
        :class="tab === 'personal' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Personal</button>
    <button type="button" data-employee-tab-btn="address" @click="setTab('address')"
        :class="tab === 'address' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Address</button>
    <button type="button" data-employee-tab-btn="job-data" @click="setTab('job-data')"
        :class="tab === 'job-data' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Job Data</button>
    <button type="button" data-employee-tab-btn="tax-data" @click="setTab('tax-data')"
        :class="tab === 'tax-data' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Tax Data</button>
    <button type="button" data-employee-tab-btn="documents" @click="setTab('documents')"
        :class="tab === 'documents' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Documents</button>
    <button type="button" data-employee-tab-btn="checklist" @click="setTab('checklist')"
        :class="tab === 'checklist' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Checklist</button>
</div>
