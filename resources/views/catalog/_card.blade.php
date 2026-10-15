<div class="product-card">
    <a href="{{ route('catalog.show', $product) }}" class="product-image">
        @if($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
        @else
            <div class="no-image">Sin imagen</div>
        @endif
    </a>

    <div class="product-info">
        <span class="product-category">{{ $product->category->name }}</span>
        <h3><a href="{{ route('catalog.show', $product) }}">{{ $product->name }}</a></h3>
        <p class="product-brand">{{ $product->brand }}</p>
        <p class="product-price">${{ number_format($product->price, 2) }}</p>

        @if($product->stock > 0)
            <p class="stock ok">Disponibles: {{ $product->stock }}</p>
        @else
            <p class="stock out">Agotado</p>
        @endif

        <div class="card-actions">
            <a href="{{ route('catalog.show', $product) }}" class="btn-view">Ver detalle</a>

            @if($product->stock > 0)
                @auth
                    <form action="{{ route('cart.store', $product) }}" method="POST" class="inline-form">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn">Agregar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn">Agregar</a>
                @endauth
            @endif
        </div>
    </div>
</div>
