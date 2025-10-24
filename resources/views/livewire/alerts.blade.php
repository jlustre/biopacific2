<div>
    @if ($success)
    <div class="alert alert-success">
        {{ $success }}
    </div>
    @endif

    @if ($error)
    <div class="alert alert-danger">
        {{ $error }}
    </div>
    @endif

    @if (!empty($errors))
    <div class="alert alert-warning">
        <ul>
            @foreach ($errors as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>