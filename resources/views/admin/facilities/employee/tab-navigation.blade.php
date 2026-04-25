<div class="flex space-x-2 my-4 border-b border-teal-500">
    
    <button type="button" @click="setTab('personal')"
        :class="tab === 'personal' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Personal</button>
    <button type="button" @click="setTab('address')"
        :class="tab === 'address' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Address</button>
    <button type="button" @click="setTab('assignment')"
        :class="tab === 'assignment' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Assignment</button>
    <button type="button" @click="setTab('checklist')"
        :class="tab === 'checklist' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Checklist</button>
    <button type="button" @click="setTab('documents')"
        :class="tab === 'documents' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
        class="px-4 py-2 rounded">Documents</button>
</div>