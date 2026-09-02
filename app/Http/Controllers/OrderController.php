<?php

namespace App\Http\Controllers;
 
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class OrderController extends Controller
{
    // READ - Show all orders
    public function home()
    {
        $orders = Order::all();

        return view('orders.home', [
            'orders' => $orders
        ]);
    }
    public function create(){
        return view('orders.create');
    }

  // STORE - Save a new course in the database
    public function store(Request $request)
    {
                // Validate the form fields
        $validated = $request->validate([
            'invoice_number' => 'required',
            'customer_number' => 'required',
            'customer_name' => 'required',
            'fiscal_data' => 'required',
            'address' => 'required',
            'status' => 'required',
            'price' => 'required',
            'notes' => 'nullable',
 
            // The image is optional and can have a maximum size of 2 MB
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('orders', 'public');
        }
 
        Order::create($validated);
 
        return redirect()->route('orders.home')
            ->with('success', 'Order created successfully');
    }   

    //SHOW
    public function show(Order $order){
        return view('orders.show', ['order' => $order]);
    }

    //update show
    public function edit(Order $order){
        return view('orders.edit', ['order' => $order]);
    }

      // UPD - Save a course in the database
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'invoice_number' => 'required',
            'customer_number' => 'required',
            'customer_name' => 'required',
            'fiscal_data' => 'required',
            'address' => 'required',
            'status' => 'required',
            'price' => 'required',
            'notes' => 'nullable',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($order->image) {
                Storage::disk('public')->delete($order->image);
            }
            $validated['image'] = $request->file('image')
                ->store('orders', 'public');
        }
 
        $order->update($validated);
 
        return redirect()->route('orders.home')
            ->with('success', 'Order updated successfully');
    } 

    //destroy
    public function destroy(Order $order){
        $order->delete();

        return redirect()->route('orders.home')->with('success', 'Order deleted successfully');
    }

    }