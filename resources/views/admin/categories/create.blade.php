@extends('plantilla')

@section('title', 'Nueva categoría')

@section('content')

<div class="form-container narrow">
    <h2>Nueva categoría</h2>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        @include('admin.categories._form')
    </form>
</div>

@endsection
