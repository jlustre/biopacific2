<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-indigo-600 text-3xl mb-2"><i class="fas fa-hospital"></i></div>
        <div class="text-2xl font-bold">{{ \App\Models\Facility::count() }}</div>
        <div class="text-sm text-gray-500">Facilities</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-pink-500 text-3xl mb-2"><i class="fas fa-users"></i></div>
        <div class="text-2xl font-bold">{{ \App\Models\User::count() }}</div>
        <div class="text-sm text-gray-500">Users</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-green-500 text-3xl mb-2"><i class="fas fa-question-circle"></i></div>
        <div class="text-2xl font-bold">{{ \App\Models\Faq::count() }}</div>
        <div class="text-sm text-gray-500">FAQs</div>
    </div>
</div>