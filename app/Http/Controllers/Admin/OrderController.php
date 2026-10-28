<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $orders = Order::with('user')
            ->when(array_key_exists((string) $status, Order::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders, 'status' => $status]);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'user']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
        ]);

        if (! $order->changeStatus($data['status'])) {
            return back()->with('error', 'Ese cambio de estado no está permitido para este pedido.');
        }

        return back()->with('success', "Pedido {$order->number} actualizado a «{$order->status_label}».");
    }
}
