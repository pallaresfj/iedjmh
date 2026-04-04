@extends('layouts.public.app')

@section('title', $news['title'])
@section('meta_description', $news['excerpt'])
@if (! empty($news['image_url']))
    @section('meta_image', $news['image_url'])
@endif

@section('content')
    <x-public.internal-page :title="$news['title']" :lead="$news['excerpt']" section-key="noticias" :force-banner-title-style="true">
        <x-slot:sidebar>
            <div class="public-surface p-4 sm:p-5">
                <p class="public-heading text-sm font-semibold uppercase tracking-wide text-ied-gray-900">Volver</p>
                <a href="{{ route('noticias.index') }}" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ied-primary-dark hover:text-ied-primary">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H6.06l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 1.06L6.06 9.25h10.19A.75.75 0 0117 10z" clip-rule="evenodd" />
                    </svg>
                    Listado de noticias
                </a>
            </div>
        </x-slot:sidebar>

        <div class="space-y-6">
            <section class="public-surface p-5 sm:p-6">
                @if (! empty($news['image_url']))
                    <img src="{{ $news['image_url'] }}" alt="{{ $news['title'] }}" class="h-56 w-full rounded-xl object-cover sm:h-72" loading="lazy" />
                @endif

                <dl class="mt-4 grid gap-3 text-sm text-ied-gray-700 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Publicado</dt>
                        <dd>{{ $news['published_at'] ?? 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-ied-gray-900">Destacado</dt>
                        <dd>{{ $news['is_featured'] ? 'Si' : 'No' }}</dd>
                    </div>
                </dl>

                @if (collect($news['categories'])->isNotEmpty())
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($news['categories'] as $category)
                            <li class="rounded-full border border-ied-primary/20 bg-ied-primary/5 px-2.5 py-1 text-xs font-medium text-ied-primary-dark">
                                {{ $category['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($news['content']))
                    <div class="public-rich-content mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {!! $news['content'] !!}
                    </div>
                @elseif (! empty($news['excerpt']))
                    <p class="mt-5 border-t border-ied-gray-200 pt-4 text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                        {{ $news['excerpt'] }}
                    </p>
                @endif
            </section>

            @if ($related->isNotEmpty())
                <section class="space-y-4 border-t border-ied-gray-200 pt-6">
                    <h2 class="public-heading text-xl font-semibold text-ied-gray-900">Noticias relacionadas</h2>
                    <div class="grid gap-6 md:grid-cols-2">
                        @foreach ($related as $item)
                            <x-public.home.news-card
                                :title="$item['title']"
                                :excerpt="$item['excerpt']"
                                :date="$item['published_at']"
                                :url="$item['detail_url']"
                                :image-url="$item['image_url']"
                                :badge="$item['categories'][0]['name'] ?? null"
                            />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </x-public.internal-page>
@endsection
