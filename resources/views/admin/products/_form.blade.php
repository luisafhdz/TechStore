@if($errors->any())
    <div class="error-message">
        <p>Revisa los siguientes errores:</p>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<label for="category_id">Categoría</label>
<select id="category_id" name="category_id" required>
    <option value="">— Selecciona —</option>
    @foreach($categories as $cat)
        <option value="{{ $cat->id }}"
            {{ (string) old('category_id', $product->category_id ?? '') === (string) $cat->id ? 'selected' : '' }}>
            {{ $cat->full_name }}
        </option>
    @endforeach
</select>

<label for="name">Nombre</label>
<input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>

<label for="brand">Marca</label>
<input type="text" id="brand" name="brand" value="{{ old('brand', $product->brand ?? '') }}" required>

<label for="description">Descripción</label>
<textarea id="description" name="description" required>{{ old('description', $product->description ?? '') }}</textarea>

<label for="price">Precio</label>
<input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" required>

<label for="stock">Existencias</label>
<input type="number" id="stock" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>

<label for="status">Estado</label>
<select id="status" name="status" required>
    <option value="active" {{ old('status', $product->status ?? 'active') === 'active' ? 'selected' : '' }}>Activo (visible en el catálogo)</option>
    <option value="inactive" {{ old('status', $product->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactivo (oculto)</option>
</select>

<label for="image">Imagen (jpg, png o webp, máx. 2 MB)</label>
@if(!empty($product->image))
    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" width="150" class="preview-image">
    <p class="help-text">Si subes una nueva imagen, reemplazará la actual.</p>
@endif
<input type="file" id="image" name="image" accept="image/*">

<br>
<button type="submit" class="btn">Guardar</button>
<a href="{{ route('admin.products.index') }}" class="btn-view">Cancelar</a>
