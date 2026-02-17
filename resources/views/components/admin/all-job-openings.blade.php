<h2 class="text-xl font-semibold mb-2 mt-4">All Job Openings</h2>
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full table-auto">
        <thead>
            <tr>
                <th class="px-4 py-2">Title</th>
                <th class="px-4 py-2">Reporting To</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            <tr>
                <td class="border px-4 py-2">{{ $job->title }}</td>
                <td class="border px-4 py-2">{{ $job->reporting_to }}</td>
                <td class="border px-4 py-2">
                    <form method="POST" action="{{ route('admin.facility.job_openings.update', [$facility, $job]) }}">
                        @csrf
                        @method('PUT')
                        <select name="status" onchange="this.form.submit()" class="form-select">
                            <option value="open" @if($job->status=='open') selected @endif>Open</option>
                            <option value="closed" @if($job->status=='closed') selected @endif>Closed</option>
                        </select>
                    </form>
                </td>
                <td class="border px-4 py-2 flex gap-2">
                    <!-- Edit Icon -->
                    <a href="{{ route('admin.facility.job_openings.edit', [$facility, $job]) }}" title="Edit"
                        class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </a>
                    <!-- View Icon -->
                    <a href="{{ route('admin.facility.job_openings.show', [$facility, $job]) }}" title="View"
                        class="text-green-600 hover:text-green-800">
                        <i class="fas fa-eye"></i>
                    </a>
                    <!-- Delete Icon -->
                    <form method="POST" action="{{ route('admin.facility.job_openings.destroy', [$facility, $job]) }}"
                        style="display:inline;" onsubmit="return confirm('Delete this job opening?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete" class="text-red-600 hover:text-red-800"
                            style="background:none; border:none; padding:0;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>