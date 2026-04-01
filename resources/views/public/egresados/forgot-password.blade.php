@extends('layouts.public.app')

@section('title', 'Recuperar Contrasena')

@section('content')
    <section class="mx-auto flex w-full max-w-xl items-center px-4 py-10">
        <section class="w-full rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-black tracking-[-0.02em] text-ied-gray-900">Recuperar contrasena</h1>
            <p class="mt-2 text-sm text-ied-gray-700">Ingresa tu correo para recibir el enlace de restablecimiento.</p>

            @if (session('status'))
                <div class="mt-4 rounded-xl border border-ied-primary/30 bg-ied-primary/10 px-4 py-3 text-sm font-medium text-ied-primary-dark">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('egresados.password.email') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Correo electronico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-ied-primary/20" />
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-ied-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-ied-primary-dark">
                    Enviar enlace
                </button>

                <a href="{{ route('egresados.index') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-ied-gray-300 px-5 py-3 text-sm font-semibold text-ied-gray-700 transition hover:bg-ied-gray-100">
                    Volver al ingreso
                </a>
            </form>
        </section>
    </section>
@endsection
