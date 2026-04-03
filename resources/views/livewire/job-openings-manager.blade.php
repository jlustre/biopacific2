<div>
    <h2 class="text-xl font-bold mb-4">Job Openings</h2>
    @if(isset($jobOpenings) && count($jobOpenings) > 0)
    <ul>
        @foreach($jobOpenings as $job)
        <li class="mb-2">
            <strong>{{ $job->title }}</strong><br>
            <span>{{ $job->description }}</span>
        </li>
        @endforeach
    </ul>
    @else
    <p>No job openings available at this time.</p>
    @endif
</div>