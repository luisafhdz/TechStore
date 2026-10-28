@extends('plantilla')

@section('title', 'Pedido ' . $order->number)

@section('content')

<p class="breadcrumb"><a href="{{ route('orders.index') }}">← Mis pedidos</a></p>

<h2>Pedido {{ $order->number }}
    <span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span>
</h2>

@if($order->status !== 'cancelled')
    @php($flow = ['pending', 'processing', 'shipped', 'delivered'])
    <ol class="steps">
        @foreach($flow as $step)
            <li class="{{ array_search($step, $flow) <= array_search($order->status, $flow) ? 'done' : '' }}">
                {{ \App\Models\Order::STATUSES[$step] }}
            </li>
        @endforeach
    </ol>
@endif

<div class="info-grid">
    <div class="info-box">
        <h3>Envío</h3>
        <p>{{ $order->shipping_name }}</p>
        @if($order->shipping_phone)<p>Tel. {{ $order->shipping_phone }}</p>@endif
        <p>{{ $order->shipping_address }}</p>
    </div>
    <div class="info-box">
        <h3>Pedido</h3>
        <p>Fecha: {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p>Pago: {{ $order->payment_label }}</p>
    </div>
</div>

<table class="data-table">
    <thead>
        <tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
            <tr>
                <td class="left">{{ $item->product_name }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="right"><strong>Total</strong></td>
            <td><strong>${{ number_format($order->total, 2) }}</strong></td>
        </tr>
    </tfoot>
</table>

@if($order->isCancellableByCustomer())
    <form action="{{ route('orders.cancel', $order) }}" method="POST" class="cart-actions"
          onsubmit="return confirm('¿Cancelar este pedido?');">
        @csrf
        <button type="submit" class="btn-delete">Cancelar pedido</button>
    </form>
@endif

@endsection
