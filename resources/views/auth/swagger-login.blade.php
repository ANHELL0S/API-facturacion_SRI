@extends('layouts.swagger')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-softec-blue to-softec-dark px-6">
        <div class="relative w-full max-w-md bg-gray-800 p-8 rounded-xl shadow-xl border border-blue-500/20">
            <!-- Back Button -->
            <a href="/" class="absolute top-4 left-4 flex items-center text-blue-300 hover:text-blue-400 transition">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns所在="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver
            </a>

            <!-- Header -->
            <h2 class="text-3xl font-bold text-center text-white mb-6">Acceder a {{ config('app.name') }}</h2>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="bg-red-900/50 border border-red-500/50 text-red-300 p-4 rounded-lg mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Success Messages -->
            @if (session('message'))
                <div class="bg-green-900/50 border border-green-500/50 text-green-300 p-4 rounded-lg mb-6">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('swagger.login') }}" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-blue-300">Correo electrónico</label>
                    <input type="email" name="email" required autofocus
                        class="mt-1 w-full px-4 py-3 bg-gray-900/50 border border-gray-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-blue-300">Contraseña</label>
                    <input type="password" name="password" required
                        class="mt-1 w-full px-4 py-3 bg-gray-900/50 border border-gray-700 text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white font-medium py-3 rounded-lg hover:bg-blue-700 transition duration-200 shadow-md">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
@endsection