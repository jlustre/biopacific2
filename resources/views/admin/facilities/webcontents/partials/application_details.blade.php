<div>
    <h4 class="text-lg font-semibold mb-2">Applicant: {{ $jobApplication->applicant_name }}</h4>
    <p><strong>Email:</strong> {{ $jobApplication->email }}</p>
    <p><strong>Phone:</strong> {{ $jobApplication->phone }}</p>
    <p><strong>Status:</strong> {{ ucfirst($jobApplication->status) }}</p>
    <p><strong>Submitted:</strong> {{ $jobApplication->created_at->format('Y-m-d H:i') }}</p>
    <p><strong>Cover Letter:</strong></p>
    <div class="bg-gray-100 rounded p-2 mb-2">{{ $jobApplication->cover_letter }}</div>
    @if($jobApplication->resume_path)
    <p><strong>Resume:</strong> <a href="{{ asset('storage/' . $jobApplication->resume_path) }}" target="_blank"
            class="text-indigo-600 hover:underline">Download</a></p>
    @endif
</div>