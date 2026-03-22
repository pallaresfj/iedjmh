@extends('layouts.public.app')

@section('title', $title)

@section('content')
    <x-public.internal-page :title="$title" :lead="$lead" :banner="$banner" section-key="atencion" :replace-header-with-banner="true">
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
                    <article class="public-surface p-5">
                        <h2 class="public-heading text-lg font-semibold text-ied-gray-900">{{ $section['title'] }}</h2>
                        <ul class="mt-3 space-y-2 text-sm text-ied-gray-700">
                            @foreach ($section['items'] as $item)
                                <li>
                                    <a href="{{ route($item['route']) }}" class="text-ied-primary-dark hover:text-ied-primary">
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
