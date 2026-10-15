<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    // Catálogo con filtro por categoría (?category=ID) y búsqueda por nombre (?q=texto)
    public function index(Request $request)
    {
        $categories = Category::roots()->with('children')->orderBy('name')->get();

        $category = null;
        $query = Product::with('category')->active();

        if ($request->filled('category')) {
            $category = Category::find($request->input('category'));

            if ($category) {
                // Si es categoría padre, incluye los productos de sus subcategorías
                $query->whereIn('category_id', $category->idsWithChildren());
            }
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        $products = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('catalog.index', [
            'categories' => $categories,
            'products'   => $products,
            'category'   => $category,
        ]);
    }

    // Detalle de un producto
    public function show(Product $product)
    {
        abort_if($product->status !== 'active', 404);

        $product->load('category.parent');

        return view('catalog.show', ['product' => $product]);
    }
}
