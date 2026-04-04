@extends('layouts.public.app')

@section('title', $project['title'])

@section('content')
    @php
        $detailImages = collect();

        if (! empty($project['image_url'])) {
            $detailImages->push([
                'url' => $project['image_url'],
                'alt' => $project['title'].' - Imagen 1',
            ]);
        }

        collect($project['gallery_images'] ?? [])
            ->filter()
            ->each(function (string $url) use ($detailImages, $project): void {
                $detailImages->push([
                    'url' => $url,
                    'alt' => $project['title'].' - Imagen '.($detailImages->count() + 1),
                ]);
            });

        $detailImages = $detailImages
            ->unique('url')
            ->take(5)
            ->values();
    @endphp

    <x-public.internal-page :title="$project['title']" :lead="$project['summary']" section-key="academico" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.academico.sidebar :pages="$academicPages" />

            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('academico.proyectos-pedagogicos') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Proyectos Pedagógicos
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            {{-- Galería de imágenes --}}
            @if ($detailImages->isNotEmpty())
                <section class="rounded-2xl border border-ied-gray-200 bg-white" data-project-gallery>
                    @if ($detailImages->count() === 1)
                        {{-- 1 imagen: ancho completo --}}
                        <button
                            type="button"
                            class="block w-full overflow-hidden rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                            data-gallery-open="0"
                            aria-label="Ver imagen ampliada"
                        >
                            <img src="{{ $detailImages[0]['url'] }}" alt="{{ $detailImages[0]['alt'] }}" class="h-64 w-full object-cover sm:h-80" />
                        </button>
                    @elseif ($detailImages->count() === 2)
                        {{-- 2 imágenes: dos columnas iguales --}}
                        <div class="grid grid-cols-2 gap-1.5 overflow-hidden rounded-2xl p-1.5">
                            @foreach ($detailImages as $index => $image)
                                <button
                                    type="button"
                                    class="overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                                    data-gallery-open="{{ $index }}"
                                    aria-label="Ver {{ strtolower($image['alt']) }}"
                                >
                                    <img
                                        src="{{ $image['url'] }}"
                                        alt="{{ $image['alt'] }}"
                                        class="h-48 w-full object-cover transition duration-300 hover:scale-105 sm:h-64"
                                        data-project-gallery-thumbnail
                                    />
                                </button>
                            @endforeach
                        </div>
                    @elseif ($detailImages->count() === 3)
                        {{-- 3 imágenes: principal grande + 2 thumbnails --}}
                        <div class="grid grid-cols-2 gap-1.5 overflow-hidden rounded-2xl p-1.5">
                            <button
                                type="button"
                                class="col-span-2 overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40 sm:col-span-1 sm:row-span-2"
                                data-gallery-open="0"
                                aria-label="Ver {{ strtolower($detailImages[0]['alt']) }}"
                            >
                                <img
                                    src="{{ $detailImages[0]['url'] }}"
                                    alt="{{ $detailImages[0]['alt'] }}"
                                    class="h-48 w-full object-cover transition duration-300 hover:scale-105 sm:h-full sm:min-h-64"
                                    data-project-gallery-thumbnail
                                />
                            </button>
                            @foreach ($detailImages->slice(1) as $index => $image)
                                <button
                                    type="button"
                                    class="overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                                    data-gallery-open="{{ $index }}"
                                    aria-label="Ver {{ strtolower($image['alt']) }}"
                                >
                                    <img
                                        src="{{ $image['url'] }}"
                                        alt="{{ $image['alt'] }}"
                                        class="h-28 w-full object-cover transition duration-300 hover:scale-105 sm:h-full"
                                        data-project-gallery-thumbnail
                                    />
                                </button>
                            @endforeach
                        </div>
                    @elseif ($detailImages->count() === 4)
                        {{-- 4 imágenes: principal grande + 3 thumbnails en columna --}}
                        <div class="grid grid-cols-3 grid-rows-2 gap-1.5 overflow-hidden rounded-2xl p-1.5">
                            <button
                                type="button"
                                class="col-span-3 overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40 sm:col-span-2 sm:row-span-2"
                                data-gallery-open="0"
                                aria-label="Ver {{ strtolower($detailImages[0]['alt']) }}"
                            >
                                <img
                                    src="{{ $detailImages[0]['url'] }}"
                                    alt="{{ $detailImages[0]['alt'] }}"
                                    class="h-48 w-full object-cover transition duration-300 hover:scale-105 sm:h-full sm:min-h-64"
                                    data-project-gallery-thumbnail
                                />
                            </button>
                            @foreach ($detailImages->slice(1) as $index => $image)
                                <button
                                    type="button"
                                    class="overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                                    data-gallery-open="{{ $index }}"
                                    aria-label="Ver {{ strtolower($image['alt']) }}"
                                >
                                    <img
                                        src="{{ $image['url'] }}"
                                        alt="{{ $image['alt'] }}"
                                        class="h-28 w-full object-cover transition duration-300 hover:scale-105 sm:h-full"
                                        data-project-gallery-thumbnail
                                    />
                                </button>
                            @endforeach
                        </div>
                    @else
                        {{-- 5 imágenes: principal grande + 4 thumbnails en grid --}}
                        <div class="grid grid-cols-4 grid-rows-2 gap-1.5 overflow-hidden rounded-2xl p-1.5">
                            <button
                                type="button"
                                class="col-span-4 overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40 sm:col-span-2 sm:row-span-2"
                                data-gallery-open="0"
                                aria-label="Ver {{ strtolower($detailImages[0]['alt']) }}"
                            >
                                <img
                                    src="{{ $detailImages[0]['url'] }}"
                                    alt="{{ $detailImages[0]['alt'] }}"
                                    class="h-48 w-full object-cover transition duration-300 hover:scale-105 sm:h-full sm:min-h-64"
                                    data-project-gallery-thumbnail
                                />
                            </button>
                            @foreach ($detailImages->slice(1) as $index => $image)
                                <button
                                    type="button"
                                    class="overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                                    data-gallery-open="{{ $index }}"
                                    aria-label="Ver {{ strtolower($image['alt']) }}"
                                >
                                    <img
                                        src="{{ $image['url'] }}"
                                        alt="{{ $image['alt'] }}"
                                        class="h-28 w-full object-cover transition duration-300 hover:scale-105 sm:h-full sm:min-h-[7.5rem]"
                                        data-project-gallery-thumbnail
                                    />
                                </button>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            {{-- Metadatos y contenido --}}
            <section class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                <dl class="grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">date_range</span>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ied-gray-900">Periodo</dt>
                            <dd>{{ $project['period'] ?? 'No definido' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">event</span>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-ied-gray-900">Publicado</dt>
                            <dd>{{ $project['published_at'] ?? 'No registrado' }}</dd>
                        </div>
                    </div>
                    @if ($project['is_featured'])
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-ied-primary" aria-hidden="true">star</span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-ied-primary-dark">Proyecto destacado</span>
                        </div>
                    @endif
                </dl>

                @if (collect($project['categories'])->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($project['categories'] as $category)
                            <span class="inline-flex items-center rounded-full bg-ied-primary-light/20 px-3 py-1 text-xs font-semibold text-ied-primary-dark">
                                {{ $category['name'] }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if (! empty($project['external_url']))
                    <div class="mt-5 border-t border-ied-gray-200 pt-4">
                        <a
                            href="{{ $project['external_url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-ied-primary-dark transition hover:text-ied-primary"
                        >
                            Mas información
                            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                @endif

                @if (! empty($project['description']))
                    <div class="public-rich-content mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {!! $project['description'] !!}
                    </div>
                @endif
            </section>

            {{-- Modal lightbox --}}
            @if ($detailImages->isNotEmpty())
                <div
                    data-project-gallery-modal
                    class="fixed inset-0 z-70 hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                    aria-hidden="true"
                >
                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-label="Visor de galería del proyecto"
                        class="relative flex max-h-[92vh] w-full max-w-4xl flex-col items-center rounded-2xl bg-ied-gray-900 p-3 shadow-2xl"
                    >
                        {{-- Barra superior --}}
                        <div class="mb-2 flex w-full items-center justify-between px-1">
                            <span data-gallery-counter class="text-xs font-semibold tracking-wide text-white/70"></span>
                            <button
                                type="button"
                                data-gallery-close
                                class="inline-flex size-9 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20"
                                aria-label="Cerrar visor"
                            >
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">close</span>
                            </button>
                        </div>

                        {{-- Imagen --}}
                        <div class="relative flex min-h-0 flex-1 w-full items-center justify-center overflow-hidden">
                            @if ($detailImages->count() > 1)
                                <button
                                    type="button"
                                    data-gallery-prev
                                    class="absolute left-1 z-10 inline-flex size-10 items-center justify-center rounded-full bg-black/40 text-white transition hover:bg-black/60"
                                    aria-label="Imagen anterior"
                                >
                                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">chevron_left</span>
                                </button>
                            @endif

                            <img
                                src=""
                                alt=""
                                class="max-h-full w-auto max-w-full rounded-lg object-contain"
                                data-gallery-image
                            />

                            @if ($detailImages->count() > 1)
                                <button
                                    type="button"
                                    data-gallery-next
                                    class="absolute right-1 z-10 inline-flex size-10 items-center justify-center rounded-full bg-black/40 text-white transition hover:bg-black/60"
                                    aria-label="Imagen siguiente"
                                >
                                    <span class="material-symbols-outlined text-2xl" aria-hidden="true">chevron_right</span>
                                </button>
                            @endif
                        </div>

                        {{-- Thumbnails en modal --}}
                        @if ($detailImages->count() > 1)
                            <div class="mt-3 flex gap-2 overflow-x-auto px-1 pb-1">
                                @foreach ($detailImages as $index => $image)
                                    <button
                                        type="button"
                                        data-gallery-modal-thumb="{{ $index }}"
                                        class="size-14 shrink-0 overflow-hidden rounded-lg border-2 border-transparent opacity-50 transition hover:opacity-80"
                                        aria-label="Ir a {{ strtolower($image['alt']) }}"
                                    >
                                        <img src="{{ $image['url'] }}" alt="" class="size-full object-cover" loading="lazy" />
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Proyectos relacionados --}}
            @if ($related->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Proyectos relacionados</h2>
                    <div @class([
                        'grid gap-4',
                        'md:grid-cols-2' => $related->count() >= 2,
                    ])>
                        @foreach ($related as $item)
                            <article class="public-surface overflow-hidden">
                                @if (! empty($item['image_url']))
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="h-40 w-full object-cover" loading="lazy" />
                                @else
                                    <div class="h-40 w-full bg-linear-to-br from-ied-primary-light/35 via-ied-primary/15 to-ied-gray-100"></div>
                                @endif

                                <div class="p-5">
                                    @if ($item['is_featured'])
                                        <span class="inline-flex rounded-full bg-ied-primary/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-ied-primary-dark">Destacado</span>
                                    @endif

                                    <h3 class="public-heading mt-2 text-lg font-semibold text-ied-gray-900">
                                        <a href="{{ $item['detail_url'] }}" class="transition hover:text-ied-primary-dark">{{ $item['title'] }}</a>
                                    </h3>

                                    @if (! empty($item['summary']))
                                        <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
                                    @endif

                                    @if (! empty($item['period']))
                                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-ied-primary-dark">{{ $item['period'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection

@push('scripts')
    <script>
        (() => {
            const images = @json($detailImages->toArray());
            if (!images.length) return;

            // Precarga todas las imágenes en caché del navegador
            images.forEach((img) => { new Image().src = img.url; });

            let currentIndex = 0;
            const modal = document.querySelector('[data-project-gallery-modal]');
            const modalImage = modal?.querySelector('[data-gallery-image]');
            const counter = modal?.querySelector('[data-gallery-counter]');
            if (!modal || !modalImage) return;

            const show = (index) => {
                currentIndex = (index + images.length) % images.length;
                modalImage.src = images[currentIndex].url;
                modalImage.alt = images[currentIndex].alt;
                if (counter) counter.textContent = `${currentIndex + 1} / ${images.length}`;

                modal.querySelectorAll('[data-gallery-modal-thumb]').forEach((thumb) => {
                    const isActive = parseInt(thumb.dataset.galleryModalThumb) === currentIndex;
                    thumb.classList.toggle('border-white', isActive);
                    thumb.classList.toggle('opacity-100', isActive);
                    thumb.classList.toggle('border-transparent', !isActive);
                    thumb.classList.toggle('opacity-50', !isActive);
                });
            };

            const open = (index) => {
                show(index);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            };

            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-gallery-open]').forEach((btn) => {
                btn.addEventListener('click', () => open(parseInt(btn.dataset.galleryOpen)));
            });

            modal.querySelector('[data-gallery-close]')?.addEventListener('click', close);
            modal.querySelector('[data-gallery-prev]')?.addEventListener('click', () => show(currentIndex - 1));
            modal.querySelector('[data-gallery-next]')?.addEventListener('click', () => show(currentIndex + 1));

            modal.querySelectorAll('[data-gallery-modal-thumb]').forEach((thumb) => {
                thumb.addEventListener('click', () => show(parseInt(thumb.dataset.galleryModalThumb)));
            });

            modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

            document.addEventListener('keydown', (e) => {
                if (modal.getAttribute('aria-hidden') !== 'false') return;
                if (e.key === 'Escape') close();
                if (e.key === 'ArrowLeft') show(currentIndex - 1);
                if (e.key === 'ArrowRight') show(currentIndex + 1);
            });

            window.addEventListener('pagehide', () => document.body.classList.remove('overflow-hidden'));
        })();
    </script>
@endpush
