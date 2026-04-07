@props([
    'icon' => 'ms:help',
    'fallback' => 'ms:help',
    'label' => null,
])

@php($metadata = \App\Support\PublicIcon::metadata($icon, $fallback))
@php($iconLabel = is_string($label) ? trim($label) : '')

@if ($metadata['set'] === 'ms')
    @if ($iconLabel !== '')
        <span {{ $attributes->class([$metadata['classes']])->merge([
            'aria-hidden' => 'false',
            'role' => 'img',
            'aria-label' => $iconLabel,
        ]) }}>{{ $metadata['name'] }}</span>
    @else
        <span {{ $attributes->class([$metadata['classes']])->merge([
            'aria-hidden' => 'true',
        ]) }}>{{ $metadata['name'] }}</span>
    @endif
@else
    @if ($iconLabel !== '')
        <i {{ $attributes->class([$metadata['classes'], 'public-icon--fa'])->merge([
            'aria-hidden' => 'false',
            'role' => 'img',
            'aria-label' => $iconLabel,
        ]) }}></i>
    @else
        <i {{ $attributes->class([$metadata['classes'], 'public-icon--fa'])->merge([
            'aria-hidden' => 'true',
        ]) }}></i>
    @endif
@endif
