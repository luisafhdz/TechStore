<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::roots()
            ->with(['children' => fn ($q) => $q->withCount('products')->orderBy('name')])
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('admin.categories.create', [
            'parents' => Category::roots()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:categories,name',
            // Solo 2 niveles: el padre debe ser una categoría principal
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->whereNull('parent_id')],
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents'  => Category::roots()->where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'parent_id' => ['nullable', Rule::notIn([$category->id]), Rule::exists('categories', 'id')->whereNull('parent_id')],
        ]);

        // Una categoría con subcategorías no puede convertirse en subcategoría
        if (! empty($validated['parent_id']) && $category->children()->exists()) {
            return back()->withInput()->withErrors([
                'parent_id' => 'Esta categoría tiene subcategorías, por lo que no puede ser subcategoría de otra.',
            ]);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene subcategorías.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene productos asignados.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría eliminada.');
    }
}
