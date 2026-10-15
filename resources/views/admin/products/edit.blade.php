@extends('plantilla')

@section('title', 'Editar producto')

@section('content')

<div class="form-container">
    <h2>Editar producto</h2>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form')
    </form>
</div>

@endsection
