@extends('plantilla')

@section('title', 'Administrar productos')

@section('content')

<h2>Productos</h2>

<a href="{{ route('admin.products.create') }}" class="btn">+ Nuevo producto</a>
<br><br>

@if($products->count() > 0)

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Marca</th>
            <th>Precio</th>
            <th>Existencias</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" width="60">
                    @else
                        —
                    @endif
                </td>
                <td class="left">{{ $product->name }}</td>
                <td>{{ $product->category->full_name }}</td>
                <td>{{ $product->brand }}</td>
                <td>${{ number_format($product->price, 2) }}</td>
                <td>
                    <form action="{{ route('admin.products.stock', $product) }}" method="POST" class="inline-form">
                        @csrf
                        @method('PATCH')
                        <input type="number" name="stock" value="{{ $product->stock }}" min="0" class="qty-input">
                        <button type="submit" class="btn-view">Guardar</button>
                    </form>
                </td>
                <td>
                    <span class="badge {{ $product->status === 'active' ? 'badge-ok' : 'badge-off' }}">
                        {{ $product->status === 'active' ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td class="actions">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn-edit">Editar</a>

                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="delete-form"
                          onsubmit="return confirm('¿Eliminar este producto?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination-wrapper">
    {{ $products->links() }}
</div>

@else
    <p>No hay productos registrados.</p>
@endif

@endsection
