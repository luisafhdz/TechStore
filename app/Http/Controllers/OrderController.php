<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Resumen del carrito + datos de envío y pago (simulado)
    public function checkout(Request $request)
    {
        $items = $request->user()->cartItems()->with('product')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $total = $items->sum(fn ($item) => $item->subtotal);

        return view('pedidos.checkout', ['items' => $items, 'total' => $total]);
    }

    // Genera el pedido: valida existencias, descuenta inventario y vacía el carrito
    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_name'    => 'required|string|max:255',
            'shipping_phone'   => 'nullable|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:card,transfer,cod',
        ]);

        $user = $request->user();

        try {
            $order = DB::transaction(function () use ($user, $data) {
                $cartItems = $user->cartItems()->with('product')->get();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('Tu carrito está vacío.');
                }

                $order = Order::create(array_merge($data, [
                    'user_id' => $user->id,
                    'status'  => 'pending',
                    'total'   => 0,
                ]));

                $total = 0;

                foreach ($cartItems as $cartItem) {
                    $name = optional($cartItem->product)->name ?? 'Un producto';
                    $product = Product::whereKey($cartItem->product_id)->lockForUpdate()->first();

                    if (! $product || $product->status !== 'active') {
                        throw new \RuntimeException("«{$name}» ya no está disponible. Quítalo del carrito.");
                    }

                    if ($product->stock < $cartItem->quantity) {
                        throw new \RuntimeException("Solo quedan {$product->stock} pieza(s) de «{$product->name}». Ajusta tu carrito.");
                    }

                    $product->decrement('stock', $cartItem->quantity);

                    $order->items()->create([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'unit_price'   => $product->price,
                        'quantity'     => $cartItem->quantity,
                    ]);

                    $total += (float) $product->price * $cartItem->quantity;
                }

                $order->update(['total' => round($total, 2)]);
                $user->cartItems()->delete();

                return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Pedido {$order->number} registrado. Pago simulado exitoso.");
    }

    // Mis pedidos
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->withCount('items')->latest()->paginate(10);

        return view('pedidos.index', ['orders' => $orders]);
    }

    // Detalle de un pedido (solo el dueño)
    public function show(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $order->load('items');

        return view('pedidos.show', ['order' => $order]);
    }

    // El cliente puede cancelar mientras el pedido esté pendiente
    public function cancel(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if (! $order->isCancellableByCustomer() || ! $order->changeStatus('cancelled')) {
            return back()->with('error', 'Este pedido ya no se puede cancelar.');
        }

        return back()->with('success', "Pedido {$order->number} cancelado.");
    }
}
