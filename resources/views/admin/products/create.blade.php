@extends('plantilla')

@section('title', 'Nuevo producto')

@section('content')

<div class="form-container">
    <h2>Nuevo producto</h2>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form')
    </form>
</div>

@endsection
