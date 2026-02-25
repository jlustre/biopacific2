@if(($hasPreEmployment ?? false) && ($checklistStats ?? null))
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-gray-600 text-3xl mb-2"><i class="fas fa-tasks"></i></div>
        <div class="text-2xl font-bold">{{ $checklistStats['total'] }}</div>
        <div class="text-sm text-gray-500">Total Items</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-green-600 text-3xl mb-2"><i class="fas fa-check-circle"></i></div>
        <div class="text-2xl font-bold">{{ $checklistStats['completed'] }}</div>
        <div class="text-sm text-gray-500">Completed</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-blue-600 text-3xl mb-2"><i class="fas fa-paper-plane"></i></div>
        <div class="text-2xl font-bold">{{ $checklistStats['submitted'] }}</div>
        <div class="text-sm text-gray-500">Submitted</div>
    </div>
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
        <div class="text-yellow-600 text-3xl mb-2"><i class="fas fa-edit"></i></div>
        <div class="text-2xl font-bold">{{ $checklistStats['draft'] + $checklistStats['returned'] }}</div>
        <div class="text-sm text-gray-500">In Progress</div>
    </div>
</div>
@else
<div class="bg-white rounded-xl shadow p-8 text-center">
    <div class="text-gray-400 text-5xl mb-4"><i class="fas fa-user-circle"></i></div>
    <h3 class="text-xl font-semibold text-gray-700 mb-2">Welcome to Your Dashboard</h3>
    <p class="text-gray-500">Your personalized dashboard will show relevant information based on your activities.</p>
</div>
@endif