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

        $activeImage = $detailImages->first();
    @endphp

    <x-public.internal-page :title="$project['title']" :lead="$project['summary']" section-key="proyectos">
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
                @if ($activeImage)
                    <img
                        src="{{ $activeImage['url'] }}"
                        alt="{{ $activeImage['alt'] }}"
                        class="h-60 w-full rounded-xl object-cover sm:h-72"
                        loading="lazy"
                        data-project-gallery-active-image
                    />
                @endif

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
                    <div class="mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {!! nl2br(e($project['description'])) !!}
                    </div>
                @endif

                @if ($detailImages->count() > 1)
                    <div class="mt-5 border-t border-ied-gray-200 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ied-gray-700">Galeria</p>
                        <div class="mt-3 flex gap-3 overflow-x-auto pb-1" data-project-gallery-thumbnails>
                            @foreach ($detailImages as $index => $image)
                                @php($isActive = $index === 0)
                                <button
                                    type="button"
                                    class="relative h-16 w-24 shrink-0 overflow-hidden rounded-lg border-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-ied-primary/40 {{ $isActive ? 'border-ied-primary' : 'border-transparent' }}"
                                    data-project-gallery-thumbnail
                                    data-image-src="{{ $image['url'] }}"
                                    data-image-alt="{{ $image['alt'] }}"
                                    aria-label="Ver {{ strtolower($image['alt']) }}"
                                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                                    @if ($isActive)
                                        aria-current="true"
                                    @endif
                                >
                                    <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="h-full w-full object-cover" loading="lazy" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

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
    <script>
        const initProjectGallery = () => {
            document.querySelectorAll('[data-project-gallery]').forEach((gallery) => {
                if (gallery.dataset.projectGalleryInitialized === '1') {
                    return;
                }

                gallery.dataset.projectGalleryInitialized = '1';

                const activeImage = gallery.querySelector('[data-project-gallery-active-image]');
                const thumbnails = gallery.querySelectorAll('[data-project-gallery-thumbnail]');

                if (!activeImage || thumbnails.length === 0) {
                    return;
                }

                const setActiveThumbnail = (selectedThumbnail) => {
                    thumbnails.forEach((thumbnail) => {
                        const isSelected = thumbnail === selectedThumbnail;

                        thumbnail.classList.toggle('border-ied-primary', isSelected);
                        thumbnail.classList.toggle('border-transparent', !isSelected);
                        thumbnail.setAttribute('aria-pressed', isSelected ? 'true' : 'false');

                        if (isSelected) {
                            thumbnail.setAttribute('aria-current', 'true');
                        } else {
                            thumbnail.removeAttribute('aria-current');
                        }
                    });
                };

                thumbnails.forEach((thumbnail) => {
                    thumbnail.addEventListener('click', () => {
                        const nextImageSrc = thumbnail.dataset.imageSrc;
                        const nextImageAlt = thumbnail.dataset.imageAlt;

                        if (!nextImageSrc) {
                            return;
                        }

                        activeImage.src = nextImageSrc;
                        activeImage.alt = nextImageAlt || activeImage.alt;
                        setActiveThumbnail(thumbnail);
                    });
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
