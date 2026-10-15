<a href="{{ route('admin.categories.edit', $category) }}" class="btn-edit">Editar</a>

<form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="delete-form"
      onsubmit="return confirm('¿Eliminar esta categoría?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn-delete">Eliminar</button>
</form>
