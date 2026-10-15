@extends('plantilla')

@section('title', 'Administrar categorías')

@section('content')

<h2>Categorías</h2>

<a href="{{ route('admin.categories.create') }}" class="btn">+ Nueva categoría</a>
<br><br>

<table class="data-table">
    <thead>
        <tr>
            <th>Categoría</th>
            <th>Productos</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($categories as $parent)
            <tr class="row-parent">
                <td class="left"><strong>{{ $parent->name }}</strong></td>
                <td>{{ $parent->products_count }}</td>
                <td class="actions">
                    @include('admin.categories._actions', ['category' => $parent])
                </td>
            </tr>

            @foreach($parent->children as $child)
                <tr>
                    <td class="left indent">↳ {{ $child->name }}</td>
                    <td>{{ $child->products_count }}</td>
                    <td class="actions">
                        @include('admin.categories._actions', ['category' => $child])
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

@endsection
