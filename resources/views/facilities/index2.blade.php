@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Facilities</h4>
                    <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary">Add New Facility</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($facilities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Beds</th>
                                    <th>Ranking</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facilities as $facility)
                                <tr>
                                    <td>{{ $facility->name }}</td>
                                    <td>{{ $facility->city }}</td>
                                    <td>{{ $facility->state }}</td>
                                    <td>{{ $facility->beds ?? 'N/A' }}</td>
                                    <td>
                                        @if($facility->ranking_position && $facility->ranking_total)
                                        {{ $facility->ranking_position }} of {{ $facility->ranking_total }}
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('facilities.show', $facility) }}"
                                            class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('admin.facilities.edit', $facility) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('facilities.destroy', $facility) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $facilities->links() }}
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted">No facilities found.</p>
                        <a href="{{ route('admin.facilities.create') }}" class="btn btn-primary">Add Your First
                            Facility</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection