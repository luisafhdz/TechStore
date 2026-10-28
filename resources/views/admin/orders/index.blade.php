@extends('plantilla')

@section('title', 'Administrar pedidos')

@section('content')

<h2>Pedidos</h2>

<div class="filter-tabs">
    <a href="{{ route('admin.orders.index') }}" class="{{ ! $status ? 'active' : '' }}">Todos</a>
    @foreach(\App\Models\Order::STATUSES as $key => $label)
        <a href="{{ route('admin.orders.index', ['status' => $key]) }}" class="{{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if($orders->count() > 0)

    <table class="data-table">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->number }}</td>
                    <td class="left">{{ $order->user->name }}<br><small>{{ $order->user->email }}</small></td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>${{ number_format($order->total, 2) }}</td>
                    <td><span class="badge status-{{ $order->status }}">{{ $order->status_label }}</span></td>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="btn-edit">Gestionar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $orders->links() }}
    </div>

@else
    <p>No hay pedidos con este filtro.</p>
@endif

@endsection
