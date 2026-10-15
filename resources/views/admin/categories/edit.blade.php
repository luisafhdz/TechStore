@extends('plantilla')

@section('title', 'Editar categoría')

@section('content')

<div class="form-container narrow">
    <h2>Editar categoría</h2>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.categories._form')
    </form>
</div>

@endsection
