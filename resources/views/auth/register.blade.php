@extends('plantilla')

@section('title', 'Crear cuenta')

@section('content')

<div class="form-container narrow">
    <h2>Crear cuenta</h2>

    @if($errors->any())
        <div class="error-message">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register.store') }}" method="POST">
        @csrf

        <label for="name">Nombre completo</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>

        <label for="phone">Teléfono (opcional)</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone') }}">

        <label for="password">Contraseña (mínimo 8 caracteres)</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirmation">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>

        <button type="submit" class="btn">Registrarme</button>
    </form>

    <p class="help-text">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</div>

@endsection
