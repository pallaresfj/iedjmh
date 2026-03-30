@extends('layouts.public.app')

@section('title', 'Ingreso Egresados')

@section('content')
    <x-public.internal-page
        :title="$title"
        :lead="$lead"
        :banner="$banner ?? null"
        section-key="atencion"
        :replace-header-with-banner="true"
        :force-banner-title-style="true"
        :without-sidebar="true"
    >
        @if (session('egresados_status'))
            <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('egresados_status') }}
            </div>
        @endif

        @if (session('egresados_error'))
            <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ session('egresados_error') }}
            </div>
        @endif

        @if ($errors->has('login') || $errors->has('preregistro'))
            <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ $errors->first('login') ?: $errors->first('preregistro') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm lg:p-8">
                <div class="mb-6 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                        <span class="material-symbols-outlined">login</span>
                    </span>
                    <div>
                        <h1 class="text-3xl font-black tracking-[-0.02em] text-ied-gray-900">Iniciar Sesion</h1>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-ied-gray-600">Acceso miembros</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('egresados.login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="login-national-id" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Identificacion nacional</label>
                        <input id="login-national-id" type="text" name="national_id" value="{{ old('national_id') }}" required class="w-full rounded-xl border border-ied-gray-200 bg-white px-4 py-3 text-sm text-ied-gray-900 focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="CC o TI" />
                        @error('national_id')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label for="login-password" class="block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Contrasena</label>
                            <a href="{{ route('egresados.password.request') }}" class="text-xs font-semibold text-ied-primary-dark hover:underline">Olvidaste tu clave?</a>
                        </div>
                        <input id="login-password" type="password" name="password" required class="w-full rounded-xl border border-ied-gray-200 bg-white px-4 py-3 text-sm text-ied-gray-900 focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-ied-gray-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary" />
                        Mantener sesion iniciada
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-ied-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-ied-primary-dark">
                        Acceder al Panel
                        <span class="material-symbols-outlined !text-[18px]">arrow_forward</span>
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm lg:p-8">
                <div class="mb-6 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                        <span class="material-symbols-outlined">person_add</span>
                    </span>
                    <div>
                        <h2 class="text-3xl font-black tracking-[-0.02em] text-ied-gray-900">Registro de Graduado</h2>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-ied-gray-600">Nueva cuenta autonoma</p>
                    </div>
                </div>

                <p class="mb-5 text-sm leading-relaxed text-ied-gray-700">Verificaremos tus datos con los registros institucionales cargados por la institucion.</p>

                <form method="POST" action="{{ route('egresados.preregister') }}" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="full-name" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Nombre completo</label>
                            <input id="full-name" type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Ej. Mateo Rivera" />
                            @error('full_name') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="graduate-email" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Correo electronico</label>
                            <input id="graduate-email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('email') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="graduate-phone" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Telefono</label>
                            <input id="graduate-phone" type="text" name="phone" value="{{ old('phone') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('phone') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="graduation-year" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Ano de graduacion</label>
                            <select id="graduation-year" name="graduation_year" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">Seleccionar Ano</option>
                                @foreach ($graduationYears as $year)
                                    <option value="{{ $year }}" @selected((string) old('graduation_year') === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                            @error('graduation_year') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="national-id" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Identificacion nacional</label>
                            <input id="national-id" type="text" name="national_id" value="{{ old('national_id') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('national_id') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="occupation" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Ocupacion actual</label>
                            <input id="occupation" type="text" name="current_occupation" value="{{ old('current_occupation') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('current_occupation') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="city" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Ciudad</label>
                            <input id="city" type="text" name="city" value="{{ old('city') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('city') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="country" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Pais</label>
                            <input id="country" type="text" name="country" value="{{ old('country', 'Colombia') }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('country') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Contrasena</label>
                            <input id="password" type="password" name="password" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                            @error('password') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Confirmar contrasena</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />
                        </div>
                    </div>

                    <label class="flex items-start gap-3 rounded-xl border border-ied-gray-200 bg-ied-gray-100 px-4 py-3 text-sm text-ied-gray-700">
                        <input type="checkbox" name="data_processing_consent" value="1" class="mt-1 h-4 w-4 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary" @checked(old('data_processing_consent')) />
                        <span>Acepto el tratamiento de datos personales para seguimiento institucional.</span>
                    </label>
                    @error('data_processing_consent') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-ied-primary bg-white px-5 py-3 text-sm font-bold text-ied-primary-dark transition hover:bg-emerald-50">
                        Iniciar Verificacion de Datos
                    </button>
                </form>
            </section>
        </div>
    </x-public.internal-page>
@endsection
