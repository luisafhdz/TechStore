@extends('plantilla')

@section('title', 'Order Details')

@section('content')

<h2>Order Details</h2>

<div class="details-card">

    <p><strong>ID:</strong> {{ $order->id }}</p>

    <p><strong>Invoice Number:</strong> {{ $order->invoice_number }}</p>

    <p><strong>Customer Number:</strong> {{ $order->customer_number }}</p>

    <p><strong>Customer Name:</strong> {{ $order->customer_name }}</p>

    <p><strong>Fiscal Data:</strong> {{ $order->fiscal_data }}</p>

    <p><strong>Address:</strong> {{ $order->address }}</p>

    <p><strong>Status:</strong> {{ $order->status }}</p>

    <p><strong>Price:</strong> ${{ $order->price }}</p>

    <p><strong>Notes:</strong> {{ $order->notes }}</p>

    <p><strong>Image:</strong></p>

    @if($order->image)

        <img
            class="preview-image"
            src="{{ asset('storage/' . $order->image) }}"
            alt="{{ $order->customer_name }}"
            width="300">

    @else

        <p>This order does not have an image.</p>

    @endif

</div>

<br>

<a href="{{ route('orders.edit', $order) }}" class="btn-edit">
    Edit Order
</a>

<a href="{{ route('orders.home') }}" class="btn-view">
    Back to List
</a>

@endsection