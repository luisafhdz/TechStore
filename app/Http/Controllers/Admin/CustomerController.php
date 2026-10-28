<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class CustomerController extends Controller
{
    // Información básica de los clientes
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.customers.index', ['customers' => $customers]);
    }
}
