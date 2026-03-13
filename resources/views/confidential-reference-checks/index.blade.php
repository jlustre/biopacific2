@extends('layouts.dashboard')
@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Confidential Reference Checks</h1>
    <a href="{{ route('confidential-reference-checks.create') }}" class="btn btn-primary mb-4">New Reference Check</a>
    <table class="table-auto w-full bg-white rounded shadow">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Facility</th>
                <th>Reference Name</th>
                <th>Relationship</th>
                <th>Reference Phone</th>
                <th>Reference Email</th>
                <th>Company</th>
                <th>Signed</th>
                <th>Signed Date</th>
                <th>Employment From</th>
                <th>Employment To</th>
                <th>Salary</th>
                <th>Salary Per</th>
                <th>Date Contacted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checks as $check)
            <tr>
                <td>{{ $check->id }}</td>
                <td>{{ $check->user->name ?? '-' }}</td>
                <td>{{ $check->facility->name ?? '-' }}</td>
                <td>{{ $check->reference_name }}</td>
                <td>{{ $check->relationship }}</td>
                <td>{{ $check->reference_phone }}</td>
                <td>{{ $check->reference_email }}</td>
                <td>{{ $check->company }}</td>
                <td>{{ $check->signed ? 'Yes' : 'No' }}</td>
                <td>{{ $check->signed_date }}</td>
                <td>{{ $check->employment_from }}</td>
                <td>{{ $check->employment_to }}</td>
                <td>{{ $check->salary }}</td>
                <td>{{ $check->salary_per }}</td>
                <td>{{ $check->date_contacted }}</td>
                <td>
                    <a href="{{ route('confidential-reference-checks.show', $check) }}" class="text-blue-600">View</a> |
                    <a href="{{ route('confidential-reference-checks.edit', $check) }}" class="text-yellow-600">Edit</a>
                    |
                    <form action="{{ route('confidential-reference-checks.destroy', $check) }}" method="POST"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600"
                            onclick="return confirm('Delete this reference check?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $checks->links() }}</div>
</div>
@endsection