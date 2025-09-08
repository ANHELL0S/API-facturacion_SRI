<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('dashboard');
})->name('home');

Route::get('/login', function () {
    // Verificar si el usuario ya está autenticado
    if (auth()->check()) {
        // Validar rol del usuario
        if (auth()->user()->hasRole('admin')) {
            return redirect('api/documentation');
        } else {
            return view('auth.swagger-login')->withErrors(['email' => 'You do not have permission to access this area']);
        }
    }
    // Si no está autenticado, mostrar la vista de inicio de sesión
    return view('auth.swagger-login');
})->name('swagger.login');

Route::post('/login', function () {
    $credentials = request()->only('email', 'password');

    // Validate the credentials
    if (auth()->attempt($credentials)) {
        // Validate user role
        if (!auth()->user()->hasRole('admin')) {
            return view('auth.swagger-login')->withErrors(['email' => 'You do not have permission to access this area']);
        }
        request()->session()->regenerate();
        return redirect('api/documentation');
    }

    return redirect()->back()->withErrors(['email' => 'Invalid credentials']);
})->name('swagger.login.post');

Route::post('/logout', function () {
    // Verificar si ya existe una sesión activa
    $redirection = redirect()->route('swagger.login');
    if (auth()->check()) {
        auth()->logout();
        // $redirection->with('message', 'You have been logged out successfully');
    }
    return $redirection;
})->name('swagger.logout');

Route::get('/{any}', function () {
    return view('dashboard');
})->where('any', '.*');
