@extends('plantilla')

@section('title', 'Iniciar sesión')

@section('content')

<div class="form-container narrow">
    <h2>Iniciar sesión</h2>

    @if($errors->any())
        <div class="error-message">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login.store') }}" method="POST">
        @csrf

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <label class="check-label">
            <input type="checkbox" name="remember" value="1"> Mantener sesión iniciada
        </label>

        <button type="submit" class="btn">Entrar</button>
    </form>

    <p class="help-text">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
</div>

@endsection
