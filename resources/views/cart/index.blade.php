@extends('plantilla')

@section('title', 'Mi carrito')

@section('content')

<h2>Mi carrito</h2>

@if($items->count() > 0)

    <table class="data-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td class="left">
                        <a href="{{ route('catalog.show', $item->product) }}">{{ $item->product->name }}</a>
                        <br><small>{{ $item->product->brand }}</small>
                    </td>
                    <td>${{ number_format($item->product->price, 2) }}</td>
                    <td>
                        <form action="{{ route('cart.update', $item) }}" method="POST" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantity" value="{{ $item->quantity }}"
                                   min="1" max="{{ $item->product->stock }}" class="qty-input">
                            <button type="submit" class="btn-edit">Actualizar</button>
                        </form>
                    </td>
                    <td>${{ number_format($item->subtotal, 2) }}</td>
                    <td>
                        <form action="{{ route('cart.destroy', $item) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="right"><strong>Total</strong></td>
                <td colspan="2"><strong>${{ number_format($total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p class="cart-actions">
        <a href="{{ route('catalog.index') }}" class="btn-view">Seguir comprando</a>
        <a href="{{ route('orders.checkout') }}" class="btn">Realizar pedido</a>
    </p>

@else
    <p>Tu carrito está vacío.</p>
    <a href="{{ route('catalog.index') }}" class="btn">Ir al catálogo</a>
@endif

@endsection
