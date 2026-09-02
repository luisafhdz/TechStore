@extends('plantilla')

@section('title', 'Edit Order')

@section('content')

<h2>Edit Order</h2>

@if($errors->any())
<div class="error-message">
    <p>Please check the following errors:</p>

    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data">

    @csrf
    @method('PUT')

    <label>Invoice Number</label>
    <input type="text" name="invoice_number"
        value="{{ old('invoice_number', $order->invoice_number) }}">

    <label>Customer Number</label>
    <input type="text" name="customer_number"
        value="{{ old('customer_number', $order->customer_number) }}">

    <label>Customer Name</label>
    <input type="text" name="customer_name"
        value="{{ old('customer_name', $order->customer_name) }}">

    <label>Fiscal Data</label>
    <input type="text" name="fiscal_data"
        value="{{ old('fiscal_data', $order->fiscal_data) }}">

    <label>Address</label>
    <input type="text" name="address"
        value="{{ old('address', $order->address) }}">

    <label>Status</label>
    <input type="text" name="status"
        value="{{ old('status', $order->status) }}">

    <label>Price</label>
    <input type="number" name="price"
        value="{{ old('price', $order->price) }}">

    <label>Notes</label>
    <textarea name="notes">{{ old('notes', $order->notes) }}</textarea>

    <label>Current Image</label>

    @if($order->image)

        <img class="preview-image"
            src="{{ asset('storage/'.$order->image) }}"
            alt="{{ $order->customer_name }}"
            width="200">

    @else

        <p>This order does not have an image.</p>

    @endif

    <label>New Image</label>

    <input type="file"
        name="image"
        accept=".jpg,.jpeg,.png,.webp">

    <p class="help-text">
        Leave this field empty if you do not want to change the current image.
    </p>

    <button type="submit" class="btn">
        Save Order
    </button>

</form>

<br>

<a href="{{ route('orders.home') }}" class="btn-view">
    Back to Orders
</a>

@endsection