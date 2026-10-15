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

<label for="name">Nombre</label>
<input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required>

<label for="parent_id">Categoría padre</label>
<select id="parent_id" name="parent_id">
    <option value="">— Ninguna (categoría principal) —</option>
    @foreach($parents as $parent)
        <option value="{{ $parent->id }}"
            {{ (string) old('parent_id', $category->parent_id ?? '') === (string) $parent->id ? 'selected' : '' }}>
            {{ $parent->name }}
        </option>
    @endforeach
</select>

<br>
<button type="submit" class="btn">Guardar</button>
<a href="{{ route('admin.categories.index') }}" class="btn-view">Cancelar</a>
