<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // El rol queda en "customer" por defecto (nunca viene del formulario)
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('catalog.index')
            ->with('success', '¡Cuenta creada! Bienvenido a TechStore.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.products.index');
            }

            return redirect()->intended(route('catalog.index'));
        }

        return back()
            ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index')
            ->with('success', 'Sesión cerrada.');
    }
}
