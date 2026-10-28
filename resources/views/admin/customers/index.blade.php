@extends('plantilla')

@section('title', 'Clientes')

@section('content')

<h2>Clientes</h2>

@if($customers->count() > 0)

    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Pedidos</th>
                <th>Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td class="left">{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?: '—' }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $customers->links() }}
    </div>

@else
    <p>Aún no hay clientes registrados.</p>
@endif

@endsection
