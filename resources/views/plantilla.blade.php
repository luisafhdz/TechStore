<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
 
    <header>
        <h1>My Laravel CRUD App</h1>
 
        <nav>
            <a href="{{ route('orders.home') }}">Orders</a>
            |

        </nav>
 
        <hr>
    </header>
 
    <main>
        @yield('content')
    </main>
 
    <footer>
        <hr>
        <p>Footer from plantilla</p>
    </footer>
 
</body>
</html>