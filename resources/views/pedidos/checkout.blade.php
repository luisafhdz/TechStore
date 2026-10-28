@extends('plantilla')

@section('title', 'Confirmar pedido')

@section('content')

<h2>Confirmar pedido</h2>

@if($errors->any())
    <div class="error-message">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="checkout-layout">

    <div class="checkout-summary">
        <h3>Resumen</h3>
        <table class="data-table">
            <thead>
                <tr><th>Producto</th><th>Cant.</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td class="left">{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="right"><strong>Total</strong></td>
                    <td><strong>${{ number_format($total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
        <p><a href="{{ route('cart.index') }}">← Volver al carrito</a></p>
    </div>

    <div class="form-container">
        <h3>Datos de envío y pago</h3>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf

            <label for="shipping_name">Nombre de quien recibe</label>
            <input type="text" id="shipping_name" name="shipping_name"
                   value="{{ old('shipping_name', auth()->user()->name) }}" required>

            <label for="shipping_phone">Teléfono (opcional)</label>
            <input type="text" id="shipping_phone" name="shipping_phone"
                   value="{{ old('shipping_phone', auth()->user()->phone) }}">

            <label for="shipping_address">Dirección de envío</label>
            <textarea id="shipping_address" name="shipping_address" required>{{ old('shipping_address') }}</textarea>

            <label for="payment_method">Método de pago</label>
            <select id="payment_method" name="payment_method" required>
                @foreach(\App\Models\Order::PAYMENT_METHODS as $key => $label)
                    <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <p class="help-text">El pago es simulado: no se pide ni se guarda ningún dato de tarjeta.</p>

            <button type="submit" class="btn">Confirmar pedido</button>
        </form>
    </div>

</div>

@endsection
