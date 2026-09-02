@extends('plantilla')

@section('title', 'Orders')

@section('content')

<h2>Orders List</h2>

@if(session('success'))
    <div class="success-message">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('orders.create') }}" class="btn">
    + Add New Order
</a>

<br><br>

@if($orders->count() > 0)

<table class="orders-table">

    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Invoice Number</th>
            <th>Customer Number</th>
            <th>Customer Name</th>
            <th>Fiscal Data</th>
            <th>Address</th>
            <th>Status</th>
            <th>Price</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>

    @foreach($orders as $order)

        <tr>

            <td>{{ $order->id }}</td>

            <td>
                @if($order->image)
                    <img src="{{ asset('storage/'.$order->image) }}"
                         alt="{{ $order->customer_name }}"
                         width="100">
                @else
                    No image
                @endif
            </td>

            <td>{{ $order->invoice_number }}</td>
            <td>{{ $order->customer_number }}</td>
            <td>{{ $order->customer_name }}</td>
            <td>{{ $order->fiscal_data }}</td>
            <td>{{ $order->address }}</td>
            <td>{{ $order->status }}</td>
            <td>${{ $order->price }}</td>
            <td>{{ $order->notes }}</td>

            <td class="actions">

                <a href="{{ route('orders.show',$order) }}" class="btn-view">
                    View
                </a>

                <a href="{{ route('orders.edit',$order) }}" class="btn-edit">
                    Edit
                </a>

                <form action="{{ route('orders.destroy',$order) }}"
                      method="POST"
                      class="delete-form">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn-delete">
                        Delete
                    </button>

                </form>

            </td>

        </tr>

    @endforeach

    </tbody>

</table>

@else

<p>No orders found.</p>

@endif

@endsection