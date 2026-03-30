@extends('layouts.public.app')

@section('title', 'Panel de Egresados')

@section('content')
    <div class="min-h-screen bg-[#edf1ef]">
        <div class="mx-auto grid w-full max-w-[1400px] gap-6 px-4 py-6 lg:grid-cols-[260px_minmax(0,1fr)] lg:px-6">
            <aside class="rounded-2xl border border-ied-gray-200 bg-white p-4 shadow-sm lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)]">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <span class="material-symbols-outlined">school</span>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ied-gray-600">Portal institucional</p>
                        <p class="text-sm font-bold text-ied-gray-900">Egresados IEDJMH</p>
                    </div>
                </div>

                <nav class="space-y-2 text-sm">
                    <a href="{{ route('egresados.panel.resumen') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2 font-semibold transition', 'bg-emerald-100 text-emerald-900' => $section === 'resumen', 'text-ied-gray-700 hover:bg-ied-gray-100' => $section !== 'resumen'])>
                        <span class="material-symbols-outlined !text-[19px]">dashboard</span>
                        Resumen
                    </a>
                    <a href="{{ route('egresados.panel.certificados') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2 font-semibold transition', 'bg-emerald-100 text-emerald-900' => $section === 'mis-certificados', 'text-ied-gray-700 hover:bg-ied-gray-100' => $section !== 'mis-certificados'])>
                        <span class="material-symbols-outlined !text-[19px]">description</span>
                        Mis Certificados
                    </a>
                    <a href="{{ route('egresados.panel.registro-academico') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2 font-semibold transition', 'bg-emerald-100 text-emerald-900' => $section === 'registro-academico', 'text-ied-gray-700 hover:bg-ied-gray-100' => $section !== 'registro-academico'])>
                        <span class="material-symbols-outlined !text-[19px]">fact_check</span>
                        Registro Academico
                    </a>
                    <a href="{{ route('egresados.panel.configuracion') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2 font-semibold transition', 'bg-emerald-100 text-emerald-900' => $section === 'configuracion', 'text-ied-gray-700 hover:bg-ied-gray-100' => $section !== 'configuracion'])>
                        <span class="material-symbols-outlined !text-[19px]">settings</span>
                        Configuracion
                    </a>
                </nav>

                <div class="mt-6 border-t border-ied-gray-200 pt-4">
                    <form method="POST" action="{{ route('egresados.logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-ied-primary px-4 py-2.5 text-sm font-bold text-white transition hover:bg-ied-primary-dark">
                            Cerrar sesion
                        </button>
                    </form>
                </div>
            </aside>

            <section class="space-y-6">
                @if (session('egresados_status'))
                    <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('egresados_status') }}
                    </div>
                @endif

                @if ($section === 'resumen')
                    <article class="rounded-2xl border border-emerald-200 bg-emerald-100/70 p-6 shadow-sm lg:p-8">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-800">Portal institucional</p>
                        <h1 class="mt-2 text-4xl font-black tracking-[-0.02em] text-emerald-950">Bienvenido de nuevo, {{ $graduate->full_name }}.</h1>
                        <p class="mt-4 max-w-3xl text-lg leading-relaxed text-emerald-900">Accede a tus documentos oficiales y mantente conectado con la red de egresados.</p>
                    </article>

                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
                        <div class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-3xl font-black tracking-[-0.02em] text-ied-gray-900">Documentos Digitales</h2>
                                <span class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">Registros verificados</span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                @forelse ($documents as $document)
                                    <article class="rounded-2xl border border-ied-gray-200 bg-ied-gray-100 p-4">
                                        <h3 class="text-xl font-black tracking-[-0.01em] text-ied-gray-900">{{ $document->title }}</h3>
                                        <p class="mt-1 text-sm text-ied-gray-700">{{ $document->description ?: 'Documento institucional disponible en Google Drive.' }}</p>
                                        <a href="{{ $document->drive_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-emerald-300 bg-white px-4 py-2 text-sm font-bold text-emerald-800 transition hover:bg-emerald-50">
                                            Abrir documento
                                        </a>
                                    </article>
                                @empty
                                    <p class="text-sm text-ied-gray-700">No hay documentos visibles en este momento.</p>
                                @endforelse
                            </div>
                        </div>

                        <aside class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm">
                            <h3 class="text-2xl font-black text-ied-gray-900">{{ $graduate->full_name }}</h3>
                            <p class="text-sm font-semibold text-ied-gray-700">Promocion {{ $graduate->graduation_year }}</p>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div>
                                    <dt class="font-bold uppercase tracking-[0.1em] text-ied-gray-600">Ocupacion</dt>
                                    <dd class="font-semibold text-ied-gray-900">{{ $graduate->current_occupation ?: 'No registrada' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-bold uppercase tracking-[0.1em] text-ied-gray-600">Ubicacion</dt>
                                    <dd class="font-semibold text-ied-gray-900">{{ trim(($graduate->city ?: 'Sin ciudad').', '.($graduate->country ?: 'Sin pais')) }}</dd>
                                </div>
                                <div>
                                    <dt class="font-bold uppercase tracking-[0.1em] text-ied-gray-600">Correo</dt>
                                    <dd class="font-semibold text-ied-gray-900">{{ $graduate->email ?: 'No registrado' }}</dd>
                                </div>
                            </dl>
                        </aside>
                    </div>
                @endif

                @if ($section === 'mis-certificados')
                    <article class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm">
                        <h1 class="text-3xl font-black tracking-[-0.02em] text-ied-gray-900">Mis Certificados</h1>
                        <p class="mt-2 text-sm text-ied-gray-700">Documentos oficiales publicados para tu perfil.</p>
                        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse ($documents as $document)
                                <article class="rounded-2xl border border-ied-gray-200 bg-ied-gray-100 p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-emerald-700">{{ $document->type_label ?: 'General' }}</p>
                                    <h2 class="mt-2 text-xl font-black tracking-[-0.01em] text-ied-gray-900">{{ $document->title }}</h2>
                                    <a href="{{ $document->drive_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-ied-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-ied-primary-dark">
                                        Descargar / Abrir
                                    </a>
                                </article>
                            @empty
                                <p class="text-sm text-ied-gray-700">Todavia no tienes certificados visibles.</p>
                            @endforelse
                        </div>
                    </article>
                @endif

                @if ($section === 'registro-academico')
                    <article class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm">
                        <h1 class="text-3xl font-black tracking-[-0.02em] text-ied-gray-900">Registro Academico</h1>
                        <p class="mt-2 text-sm text-ied-gray-700">Ficha academica estructurada validada por la institucion.</p>
                        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4"><dt class="text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Titulo academico</dt><dd class="mt-1 text-base font-semibold text-ied-gray-900">{{ $graduate->academic_title ?: 'No registrado' }}</dd></div>
                            <div class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4"><dt class="text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Promocion</dt><dd class="mt-1 text-base font-semibold text-ied-gray-900">{{ $graduate->graduation_year }}</dd></div>
                            <div class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4"><dt class="text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Fecha de grado</dt><dd class="mt-1 text-base font-semibold text-ied-gray-900">{{ optional($graduate->graduation_date)->format('Y-m-d') ?: 'No registrada' }}</dd></div>
                            <div class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4"><dt class="text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Acta</dt><dd class="mt-1 text-base font-semibold text-ied-gray-900">{{ $graduate->graduation_act_number ?: 'No registrada' }}</dd></div>
                            <div class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4"><dt class="text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Folio</dt><dd class="mt-1 text-base font-semibold text-ied-gray-900">{{ $graduate->graduation_folio ?: 'No registrado' }}</dd></div>
                            <div class="rounded-xl border border-ied-gray-200 bg-ied-gray-100 p-4"><dt class="text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Verificacion</dt><dd class="mt-1 text-base font-semibold text-ied-gray-900">{{ App\Models\Graduate::VERIFICATION_STATUS_OPTIONS[$graduate->record_verification_status] ?? $graduate->record_verification_status }}</dd></div>
                        </dl>
                    </article>
                @endif

                @if ($section === 'configuracion')
                    <article class="rounded-2xl border border-ied-gray-200 bg-white p-6 shadow-sm">
                        <h1 class="text-3xl font-black tracking-[-0.02em] text-ied-gray-900">Configuracion</h1>
                        <p class="mt-2 text-sm text-ied-gray-700">Actualiza tus canales de contacto y trayectoria profesional.</p>

                        <form method="POST" action="{{ route('egresados.panel.configuracion.update') }}" class="mt-6 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2"><label for="settings-full-name" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Nombre completo</label><input id="settings-full-name" type="text" name="full_name" value="{{ old('full_name', $graduate->full_name) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />@error('full_name') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror</div>
                                <div><label for="settings-email" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Correo electronico</label><input id="settings-email" type="email" name="email" value="{{ old('email', $graduate->email) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />@error('email') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror</div>
                                <div><label for="settings-phone" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Telefono</label><input id="settings-phone" type="text" name="phone" value="{{ old('phone', $graduate->phone) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />@error('phone') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror</div>
                                <div><label for="settings-occupation" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Ocupacion actual</label><input id="settings-occupation" type="text" name="current_occupation" value="{{ old('current_occupation', $graduate->current_occupation) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />@error('current_occupation') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror</div>
                                <div><label for="settings-city" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Ciudad</label><input id="settings-city" type="text" name="city" value="{{ old('city', $graduate->city) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />@error('city') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror</div>
                                <div><label for="settings-country" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-ied-gray-600">Pais</label><input id="settings-country" type="text" name="country" value="{{ old('country', $graduate->country) }}" required class="w-full rounded-xl border border-ied-gray-200 px-4 py-3 text-sm focus:border-ied-primary focus:outline-none focus:ring-2 focus:ring-emerald-200" />@error('country') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror</div>
                            </div>

                            <label class="flex items-start gap-3 rounded-xl border border-ied-gray-200 bg-ied-gray-100 px-4 py-3 text-sm text-ied-gray-700">
                                <input type="checkbox" name="data_processing_consent" value="1" class="mt-1 h-4 w-4 rounded border-ied-gray-300 text-ied-primary focus:ring-ied-primary" checked />
                                <span>Confirmo que autorizo el tratamiento de datos para seguimiento institucional.</span>
                            </label>
                            @error('data_processing_consent') <p class="text-xs font-medium text-rose-600">{{ $message }}</p> @enderror

                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-ied-primary px-6 py-3 text-sm font-bold text-white transition hover:bg-ied-primary-dark">Guardar cambios</button>
                        </form>
                    </article>
                @endif
            </section>
        </div>
    </div>
@endsection
