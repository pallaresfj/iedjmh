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

    <x-public.internal-page :title="$project['title']" :lead="$project['summary']" :banner="$banner" section-key="proyectos">
        <x-slot:sidebar>
            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('proyectos.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Listado de proyectos
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6" data-project-gallery>
                <dl class="mt-4 grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Periodo</dt>
                        <dd>{{ $project['period'] ?? 'No definido' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Publicado</dt>
                        <dd>{{ $project['published_at'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Destacado</dt>
                        <dd>{{ $project['is_featured'] ? 'Si' : 'No' }}</dd>
                    </div>
                </dl>

                @if (collect($project['categories'])->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($project['categories'] as $category)
                            <li class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-1 text-xs font-medium text-ied-primary-dark">
                                {{ $category['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($project['external_url']))
                    <div class="mt-5 border-t border-ied-gray-200 pt-4">
                        <a
                            href="{{ $project['external_url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center rounded-full bg-ied-primary px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-ied-primary-dark"
                        >
                            Mas informacion
                        </a>
                    </div>
                @endif

                @if (! empty($project['description']))
                    <div class="public-rich-content mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {!! $project['description'] !!}
                    </div>
                @endif

                @if ($detailImages->isNotEmpty())
                    <div class="mt-5 border-t border-ied-gray-200 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Galeria</p>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4" data-project-gallery-thumbnails>
                            @foreach ($detailImages as $index => $image)
                                <button
                                    type="button"
                                    class="relative h-24 w-full overflow-hidden rounded-lg border border-ied-gray-200 transition hover:border-ied-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40"
                                    style="cursor: pointer;"
                                    data-project-gallery-thumbnail
                                    data-image-src="{{ $image['url'] }}"
                                    data-image-alt="{{ $image['alt'] }}"
                                    aria-label="Ver {{ strtolower($image['alt']) }}"
                                >
                                    <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="h-full w-full object-cover" style="cursor: pointer;" loading="lazy" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            @if ($detailImages->isNotEmpty())
                <div
                    data-project-gallery-modal
                    aria-hidden="true"
                    hidden
                    style="position: fixed; inset: 0; z-index: 70; display: none; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.6); padding: 1rem;"
                >
                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-label="Visor de imagen del proyecto"
                        style="position: relative; width: min(92vw, 820px); max-height: 86vh; border-radius: 16px; background: #111827; padding: 2.8rem 1rem 1rem; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md border border-white/70 bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100"
                            style="position: absolute; top: 0.65rem; right: 0.65rem; cursor: pointer;"
                            data-project-gallery-close
                            aria-label="Cerrar visor"
                        >
                            <span aria-hidden="true" style="font-size: 1.25rem; line-height: 1;">X</span>
                        </button>

                        <img
                            src=""
                            alt=""
                            class="mx-auto block w-full rounded-lg object-contain bg-black/20"
                            style="max-height: calc(86vh - 4.2rem);"
                            data-project-gallery-modal-image
                        />
                    </div>
                </div>
            @endif

            @if ($related->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Proyectos relacionados</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($related as $item)
                            <article class="public-surface p-5">
                                @if ($item['is_featured'])
                                    <span class="inline-flex rounded-full bg-ied-primary/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-ied-primary-dark">Destacado</span>
                                @endif
                                <h3 class="public-heading mt-2 text-lg font-semibold text-ied-gray-900">
                                    <a href="{{ $item['detail_url'] }}" class="transition hover:text-ied-primary-dark">{{ $item['title'] }}</a>
                                </h3>
                                @if (! empty($item['summary']))
                                    <p class="mt-2 text-sm leading-relaxed text-ied-gray-700">{{ $item['summary'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection

@push('scripts')
    <style>
        [data-project-gallery-thumbnail] {
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        [data-project-gallery-thumbnail]:hover,
        [data-project-gallery-thumbnail]:focus-visible {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        }

        [data-project-gallery-close] {
            cursor: pointer;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const initProjectGallery = () => {
            document.querySelectorAll('[data-project-gallery]').forEach((gallery) => {
                if (gallery.dataset.projectGalleryInitialized === '1') {
                    return;
                }

                gallery.dataset.projectGalleryInitialized = '1';

                const thumbnails = gallery.querySelectorAll('[data-project-gallery-thumbnail]');
                const modal = document.querySelector('[data-project-gallery-modal]');
                const modalImage = modal?.querySelector('[data-project-gallery-modal-image]');
                const closeButton = modal?.querySelector('[data-project-gallery-close]');

                if (thumbnails.length === 0 || !modal || !modalImage) {
                    return;
                }

                const closeModal = () => {
                    modal.hidden = true;
                    modal.style.display = 'none';
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                };

                thumbnails.forEach((thumbnail) => {
                    thumbnail.addEventListener('click', () => {
                        const nextImageSrc = thumbnail.dataset.imageSrc;
                        const nextImageAlt = thumbnail.dataset.imageAlt;

                        if (!nextImageSrc) {
                            return;
                        }

                        modalImage.src = nextImageSrc;
                        modalImage.alt = nextImageAlt || 'Imagen de proyecto';
                        modal.hidden = false;
                        modal.style.display = 'flex';
                        modal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('overflow-hidden');
                    });
                });

                closeButton?.addEventListener('click', closeModal);

                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                        closeModal();
                    }
                });

                // Ensure scroll lock is cleared when leaving the page.
                window.addEventListener('pagehide', () => {
                    document.body.classList.remove('overflow-hidden');
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProjectGallery, { once: true });
        } else {
            initProjectGallery();
        }
    </script>
@endpush
