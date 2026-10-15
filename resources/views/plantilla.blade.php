<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | TechStore</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
</head>
<body>

    <header>
        <div class="topbar">
            <h1><a href="{{ route('catalog.index') }}">TechStore</a></h1>

            <form action="{{ route('catalog.index') }}" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Buscar productos..." value="{{ request('q') }}">
                <button type="submit" class="btn">Buscar</button>
            </form>

            <nav>
                <a href="{{ route('catalog.index') }}">Catálogo</a>

                @auth
                    @php($cartCount = auth()->user()->cartItems()->sum('quantity'))
                    <a href="{{ route('cart.index') }}">Carrito ({{ $cartCount }})</a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.products.index') }}">Productos</a>
                        <a href="{{ route('admin.categories.index') }}">Categorías</a>
                        <a href="{{ route('orders.home') }}">Pedidos</a>
                    @endif

                    <span class="nav-user">{{ auth()->user()->name }}</span>

                    <form action="{{ route('logout') }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="link-button">Cerrar sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                    <a href="{{ route('register') }}">Registrarse</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error-message">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer>
        <p>TechStore &copy; {{ date('Y') }} — Proyecto Integrador</p>
    </footer>

</body>
</html>
