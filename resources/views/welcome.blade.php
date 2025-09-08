@extends('layouts.swagger')

@section('content')
    <div class="bg-softec-dark text-white font-sans">
        <!-- Hero Section -->
        <section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-softec-blue to-softec-dark">
            <div class="text-center px-6">
                <h1 class="text-5xl md:text-6xl font-bold mb-4">{{ config('app.name') }}</h1>
                <p class="text-xl md:text-2xl text-blue-300 mb-8">
                    Simplifica tu facturación electrónica con una API robusta y segura
                </p>
                <a href="/login"
                    class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                    Acceder a la Documentación
                </a>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-16 px-6 bg-gray-900">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Características Principales</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        class="relative bg-gray-800 p-8 rounded-xl shadow-xl border border-blue-500/20 hover:shadow-2xl hover:-translate-y-1 hover:bg-gradient-to-b hover:from-gray-800 hover:to-gray-900 transition-all duration-300">
                        <div class="flex justify-center mb-4">
                            <div class="p-3 bg-blue-900/50 rounded-full">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg md:text-xl font-semibold text-center mb-3">Emisión de Facturas</h3>
                        <p class="text-blue-300 text-center text-sm md:text-base">Genera facturas electrónicas de manera
                            rápida y conforme a normativas.</p>
                    </div>
                    <div
                        class="relative bg-gray-800 p-8 rounded-xl shadow-xl border border-blue-500/20 hover:shadow-2xl hover:-translate-y-1 hover:bg-gradient-to-b hover:from-gray-800 hover:to-gray-900 transition-all duration-300">
                        <div class="flex justify-center mb-4">
                            <div class="p-3 bg-blue-900/50 rounded-full">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg md:text-xl font-semibold text-center mb-3">Gestión de Clientes</h3>
                        <p class="text-blue-300 text-center text-sm md:text-base">Administra clientes y sus datos de forma
                            segura y eficiente.</p>
                    </div>
                    <div
                        class="relative bg-gray-800 p-8 rounded-xl shadow-xl border border-blue-500/20 hover:shadow-2xl hover:-translate-y-1 hover:bg-gradient-to-b hover:from-gray-800 hover:to-gray-900 transition-all duration-300">
                        <div class="flex justify-center mb-4">
                            <div class="p-3 bg-blue-900/50 rounded-full">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 11c0-2.76-2.24-5-5-5S2 8.24 2 11m20 0c0-2.76-2.24-5-5-5s-5 2.24-5 5m-5 4v4m10-4v4">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg md:text-xl font-semibold text-center mb-3">Soporte Multi-Rol</h3>
                        <p class="text-blue-300 text-center text-sm md:text-base">Documentación específica para admins y
                            clientes, con acceso controlado.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Documentation Section -->
        <section class="py-16 px-6 bg-blue-950">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Documentación de la API</h2>
                <p class="text-lg text-blue-300 mb-8">
                    Accede a la documentación completa para clientes y administradores. Requiere autenticación con rol de
                    admin.
                </p>
                <a href="/login"
                    class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                    Iniciar Sesión
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 bg-softec-dark text-center">
            <p class="text-blue-300">&copy; 2025 Softec. Todos los derechos reservados.</p>
            <p class="text-blue-400 mt-2">
                <a href="mailto:soporte@softec.com" class="hover:underline">info@softec.com</a>
            </p>
        </footer>
    </div>
@endsection