@extends('layouts.public.app')

@section('title', 'Restablecer Contrasena')

@section('content')
    <section class="mx-auto flex w-full max-w-xl items-center px-4 py-10">
        <section class="w-full rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-black tracking-[-0.02em] text-ied-gray-900">Restablecer contrasena</h1>
            <p class="mt-2 text-sm text-ied-gray-700">Define una nueva contrasena para tu cuenta de egresado.</p>

            <form method="POST" action="{{ route('egresados.password.reset.update') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />

                <div>
                    <label for="email" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Correo electronico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Nueva contrasena</label>
                    <input id="password" type="password" name="password" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                    @error('password')
                        <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Confirmar contrasena</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-ied-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-ied-primary-dark">
                    Actualizar contrasena
                </button>
            </form>
        </section>
    </section>
@endsection
