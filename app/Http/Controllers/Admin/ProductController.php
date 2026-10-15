<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'brand'       => 'required|string|max:100',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    private function categoryOptions()
    {
        return Category::with('parent')->orderBy('name')->get()
            ->sortBy('full_name')->values();
    }

    public function index()
    {
        $products = Product::with('category.parent')->orderBy('name')->paginate(15);

        return view('admin.products.index', ['products' => $products]);
    }

    public function create()
    {
        return view('admin.products.create', ['categories' => $this->categoryOptions()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate($this->rules());

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // Actualización rápida de existencias desde la tabla
    public function updateStock(Request $request, Product $product)
    {
        $data = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->update(['stock' => $data['stock']]);

        return back()->with('success', "Existencias de {$product->name} actualizadas.");
    }

    public function destroy(Product $product)
    {
        // Soft delete: desaparece del catálogo pero conserva el historial
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Producto eliminado.');
    }
}
