@extends('layouts.dashboard')

@section('content')
@include('admin.blogs.create', [
'blog' => $blog,
'editMode' => true
])
@endsection