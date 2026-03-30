@if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')))
    <div class="agro-admin-login__google-section mt-5">
        <div class="agro-login-separator" aria-hidden="true">
            <span>O CONTINUAR CON</span>
        </div>

        <x-filament::button
            href="{{ route('auth.google.redirect') }}"
            color="gray"
            outlined
            tag="a"
            class="agro-google-btn w-full justify-center"
        >
            <svg class="agro-google-logo mr-2.5 h-[18px] w-[18px]" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.655 32.657 29.196 36 24 36c-6.627 0-12-5.373-12-12S17.373 12 24 12c3.059 0 5.84 1.153 7.954 3.046l5.657-5.657C34.046 6.053 29.28 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 19.001 12 24 12c3.059 0 5.84 1.153 7.954 3.046l5.657-5.657C34.046 6.053 29.28 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                <path fill="#4CAF50" d="M24 44c5.178 0 9.86-1.977 13.409-5.196l-6.19-5.238C29.152 35.088 26.682 36 24 36c-5.175 0-9.623-3.327-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.793 2.301-2.273 4.277-4.084 5.566l.003-.002 6.19 5.238C37.002 39.173 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
            </svg>
            <span class="agro-google-btn__label">Continuar con Google</span>
        </x-filament::button>
    </div>
@endif
