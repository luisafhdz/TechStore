@extends('plantilla')

@section('title', $product->name)

@section('content')

<p class="breadcrumb">
    <a href="{{ route('catalog.index') }}">Catálogo</a>
    @if($product->category->parent)
        › <a href="{{ route('catalog.index', ['category' => $product->category->parent->id]) }}">{{ $product->category->parent->name }}</a>
    @endif
    › <a href="{{ route('catalog.index', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a>
</p>

<div class="product-detail">

    <div class="detail-image">
        @if($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
        @else
            <div class="no-image big">Sin imagen</div>
        @endif
    </div>

    <div class="detail-info">
        <h2>{{ $product->name }}</h2>
        <p class="product-brand">Marca: <strong>{{ $product->brand }}</strong></p>
        <p class="product-price big">${{ number_format($product->price, 2) }}</p>

        @if($product->stock > 0)
            <p class="stock ok">Existencia: {{ $product->stock }} pieza(s)</p>
        @else
            <p class="stock out">Producto agotado</p>
        @endif

        <p class="description">{{ $product->description }}</p>

        @if($product->stock > 0)
            @auth
                <form action="{{ route('cart.store', $product) }}" method="POST" class="add-form">
                    @csrf
                    <label for="quantity">Cantidad</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="qty-input">
                    <button type="submit" class="btn">Agregar al carrito</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn">Inicia sesión para comprar</a>
            @endauth
        @endif
    </div>

</div>

@endsection
