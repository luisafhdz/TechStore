@extends('plantilla')

@section('title', 'Pedido ' . $order->number)

@section('content')

<p class="breadcrumb"><a href="{{ route('admin.orders.index') }}">← Pedidos</a></p>

<h2>Pedido {{ $order->number }}
    <span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span>
</h2>

<div class="info-grid">
    <div class="info-box">
        <h3>Cliente</h3>
        <p>{{ $order->user->name }}</p>
        <p>{{ $order->user->email }}</p>
        @if($order->user->phone)<p>Tel. {{ $order->user->phone }}</p>@endif
    </div>
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

<div class="info-box status-box">
    <h3>Actualizar estado</h3>

    @if(count($order->nextStatuses()) > 0)
        <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="status-form">
            @csrf
            @method('PATCH')
            <select name="status" required>
                @foreach($order->nextStatuses() as $next)
                    <option value="{{ $next }}">{{ \App\Models\Order::STATUSES[$next] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Actualizar</button>
        </form>
        <p class="help-text">Al cancelar, las piezas regresan al inventario.</p>
    @else
        <p>Este pedido está en un estado final ({{ $order->status_label }}); ya no admite cambios.</p>
    @endif
</div>

@endsection
