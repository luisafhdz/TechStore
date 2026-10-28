@extends('plantilla')

@section('title', 'Mis pedidos')

@section('content')

<h2>Mis pedidos</h2>

@if($orders->count() > 0)

    <table class="data-table">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->items_count }}</td>
                    <td>${{ number_format($order->total, 2) }}</td>
                    <td><span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span></td>
                    <td><a href="{{ route('orders.show', $order) }}" class="btn-view">Ver detalle</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $orders->links() }}
    </div>

@else
    <p>Aún no tienes pedidos.</p>
    <a href="{{ route('catalog.index') }}" class="btn">Ir al catálogo</a>
@endif

@endsection
