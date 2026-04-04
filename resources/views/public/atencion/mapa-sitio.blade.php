@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" section-key="atencion" :replace-header-with-banner="true" :force-banner-title-style="true">
        <x-slot:sidebar>
            <x-public.atencion.sidebar :pages="$attentionPages" />
        </x-slot:sidebar>

        <div class="space-y-6">
            @if (filled($content))
                <section class="text-sm leading-relaxed text-ied-gray-700 sm:text-base">
                    {!! nl2br(e($content)) !!}
                </section>
            @endif

            <section class="grid gap-4 md:grid-cols-2">
                @foreach ($sitemap as $section)
                    <article class="rounded-2xl border border-ied-gray-200 bg-white p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl bg-ied-primary-light/30">
                                <span class="material-symbols-outlined text-2xl text-ied-primary" aria-hidden="true">{{ $section['icon'] ?? 'article' }}</span>
                            </span>

                            <div class="min-w-0">
                                <h2 class="public-heading text-lg font-extrabold text-ied-gray-900">{{ $section['title'] }}</h2>
                            </div>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($section['items'] as $item)
                                <li class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base text-ied-gray-400" aria-hidden="true">chevron_right</span>
                                    <a href="{{ route($item['route']) }}" class="text-ied-primary-dark transition hover:text-ied-primary">
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </section>
        </div>
    </x-public.internal-page>
@endsection
