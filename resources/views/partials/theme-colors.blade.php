@php($themeColors = \App\Support\PublicSettings::themeColors())
<style>
    :root {
@foreach ($themeColors as $cssVar => $value)
        {{ $cssVar }}: {{ $value }};
@endforeach
    }
</style>
