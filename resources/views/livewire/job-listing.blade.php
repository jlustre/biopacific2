<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b">
        <h2 class="text-2xl font-bold">Job Listings ({{ count($jobs) }})</h2>
    </div>

    @if($jobs->isEmpty())
    <div class="p-6 text-center text-gray-500">
        No job listings yet. Create one above!
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Title</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Department</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Active</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($jobs as $job)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold">{{ $job->title }}</td>
                    <td class="px-6 py-4">{{ $job->department ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $job->employment_type }}</td>
                    <td class="px-6 py-4">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $job->status === 'open' ? 'bg-green-100 text-green-800' : 
                                   ($job->status === 'closed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($job->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $job->active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $job->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 space-x-2">
                        <button type="button" @click="viewModalJobId = {{ $job->id }}" title="View job details"
                            class="text-blue-600 hover:text-blue-800 font-semibold transition cursor-pointer">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button type="button" @click="editJob({{ $job->id }})" title="Edit this job listing"
                            class="text-green-600 hover:text-green-800 font-semibold transition cursor-pointer">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" @click="copyJob({{ $job->id }})" title="Copy and create new job listing"
                            class="text-indigo-600 hover:text-indigo-800 font-semibold transition cursor-pointer">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        <button type="button"
                            @click="actionType = 'toggle'; actionModalJobId = {{ $job->id }}; actionModalTitle = '{{ $job->active ? 'Deactivate' : 'Activate' }} Job Posting'; actionMessage = 'Are you sure you want to {{ $job->active ? 'deactivate' : 'activate' }} this job posting?'; showActionModal = true"
                            title="{{ $job->active ? 'Deactivate job posting' : 'Activate job posting' }}"
                            class="text-orange-600 hover:text-orange-800 font-semibold transition cursor-pointer">
                            <i class="fas fa-{{ $job->active ? 'ban' : 'check' }}"></i>
                        </button>
                        <button type="button"
                            @click="actionType = 'status'; actionModalJobId = {{ $job->id }}; actionModalTitle = 'Toggle Job Status'; actionMessage = 'Change status from {{ ucfirst($job->status) }} to {{ $job->status === 'open' ? 'Closed' : 'Open' }}?'; showActionModal = true"
                            title="Toggle status ({{ $job->status === 'open' ? 'Open' : 'Closed' }} to {{ $job->status === 'open' ? 'Closed' : 'Open' }})"
                            class="text-purple-600 hover:text-purple-800 font-semibold transition cursor-pointer">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button type="button"
                            @click="actionType = 'delete'; actionModalJobId = {{ $job->id }}; actionModalTitle = 'Delete Job'; actionMessage = 'Are you sure you want to permanently delete this job posting?'; showActionModal = true"
                            title="Delete this job listing permanently"
                            class="text-red-600 hover:text-red-800 font-semibold transition cursor-pointer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>