@if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')))
    <div class="mt-4">
        <div class="my-4 border-t border-gray-200 dark:border-white/10"></div>

        <x-filament::button
            href="{{ route('auth.google.redirect') }}"
            color="gray"
            outlined
            tag="a"
            class="w-full justify-center"
        >
            <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full border border-current text-[10px] font-bold">G</span>
            Continuar con Google
        </x-filament::button>
    </div>
@endif
