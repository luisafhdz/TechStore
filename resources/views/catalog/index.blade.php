@extends('plantilla')

@section('title', 'Catálogo')

@section('content')

<div class="catalog-layout">

    <aside class="sidebar">
        <h3>Categorías</h3>

        <ul class="category-list">
            <li class="{{ ! $category ? 'active' : '' }}">
                <a href="{{ route('catalog.index', array_filter(['q' => request('q')])) }}">Todas</a>
            </li>

            @foreach($categories as $parent)
                <li class="{{ $category && $category->id === $parent->id ? 'active' : '' }}">
                    <a href="{{ route('catalog.index', array_filter(['category' => $parent->id, 'q' => request('q')])) }}">
                        {{ $parent->name }}
                    </a>

                    @if($parent->children->isNotEmpty())
                        <ul>
                            @foreach($parent->children->sortBy('name') as $child)
                                <li class="{{ $category && $category->id === $child->id ? 'active' : '' }}">
                                    <a href="{{ route('catalog.index', array_filter(['category' => $child->id, 'q' => request('q')])) }}">
                                        {{ $child->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </aside>

    <section class="catalog-content">
        <h2>
            {{ $category ? $category->name : 'Todos los productos' }}
            @if(request('q'))
                <small>— resultados para "{{ request('q') }}"</small>
            @endif
        </h2>

        @if($products->count() > 0)
            <div class="product-grid">
                @foreach($products as $product)
                    @include('catalog._card', ['product' => $product])
                @endforeach
            </div>

            <div class="pagination-wrapper">
                {{ $products->links() }}
            </div>
        @else
            <p>No se encontraron productos.</p>
        @endif
    </section>

</div>

@endsection
