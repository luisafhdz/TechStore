<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->cartItems()->with('product')->get();
        $total = $items->sum(fn ($item) => $item->subtotal);

        return view('cart.index', ['items' => $items, 'total' => $total]);
    }

    // Agregar producto (si ya existe en el carrito, suma la cantidad)
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'nullable|integer|min:1',
        ]);
        $quantity = $data['quantity'] ?? 1;

        if ($product->status !== 'active' || $product->stock < 1) {
            return back()->with('error', 'Este producto no está disponible.');
        }

        $item = CartItem::firstOrNew([
            'user_id'    => $request->user()->id,
            'product_id' => $product->id,
        ]);

        $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

        if ($newQuantity > $product->stock) {
            return back()->with('error', "Solo hay {$product->stock} pieza(s) disponibles de {$product->name}.");
        }

        $item->quantity = $newQuantity;
        $item->save();

        return back()->with('success', "{$product->name} se agregó al carrito.");
    }

    // Modificar cantidad
    public function update(Request $request, CartItem $item)
    {
        abort_if($item->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($data['quantity'] > $item->product->stock) {
            return back()->with('error', "Solo hay {$item->product->stock} pieza(s) disponibles.");
        }

        $item->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Cantidad actualizada.');
    }

    // Eliminar del carrito
    public function destroy(Request $request, CartItem $item)
    {
        abort_if($item->user_id !== $request->user()->id, 403);

        $item->delete();

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
