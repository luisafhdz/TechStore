@extends('plantilla')

@section('title', 'Create Order')

@section('content')

<h2>Create Order</h2>

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

<form action="{{ route('orders.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <label>Invoice Number</label>
    <input type="text"
           name="invoice_number"
           value="{{ old('invoice_number') }}">

    <label>Customer Number</label>
    <input type="text"
           name="customer_number"
           value="{{ old('customer_number') }}">

    <label>Customer Name</label>
    <input type="text"
           name="customer_name"
           value="{{ old('customer_name') }}">

    <label>Fiscal Data</label>
    <input type="text"
           name="fiscal_data"
           value="{{ old('fiscal_data') }}">

    <label>Address</label>
    <input type="text"
           name="address"
           value="{{ old('address') }}">

    <label>Status</label>
    <input type="text"
           name="status"
           value="{{ old('status') }}">

    <label>Price</label>
    <input type="number"
           name="price"
           value="{{ old('price') }}">

    <label>Notes</label>
    <textarea name="notes">{{ old('notes') }}</textarea>

    <label>Order Image</label>

    <input type="file"
           name="image"
           accept=".jpg,.jpeg,.png,.webp">

    <p class="help-text">
        Allowed formats: JPG, JPEG, PNG and WEBP.<br>
        Maximum size: 2 MB.
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