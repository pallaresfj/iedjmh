<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless($this->isGoogleOauthConfigured(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless($this->isGoogleOauthConfigured(), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return $this->failedLoginResponse('No fue posible completar la autenticacion con Google. Intenta nuevamente.');
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $isGoogleEmailVerified = filter_var(
            data_get($googleUser->getRaw(), 'email_verified', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($email === '' || ! $isGoogleEmailVerified) {
            return $this->failedLoginResponse('Tu cuenta de Google no tiene un correo verificado para iniciar sesion.');
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user instanceof User) {
            return $this->failedLoginResponse('Tu correo de Google no esta autorizado en el sistema.');
        }

        $adminPanel = Filament::getPanel('admin', false);

        if ($adminPanel === null || ! $user->canAccessPanel($adminPanel)) {
            return $this->failedLoginResponse('Tu cuenta existe, pero no tiene permisos para ingresar al panel administrativo.');
        }

        if ($this->shouldRedirectToTwoFactorChallenge($user)) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => true,
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()->route('two-factor.login');
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    private function shouldRedirectToTwoFactorChallenge(User $user): bool
    {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            return false;
        }

        if (! in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user), true)) {
            return false;
        }

        if (blank($user->two_factor_secret)) {
            return false;
        }

        if (! Fortify::confirmsTwoFactorAuthentication()) {
            return true;
        }

        return ! is_null($user->two_factor_confirmed_at);
    }

    private function failedLoginResponse(string $message): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->withErrors([
                'google' => $message,
            ]);
    }

    private function isGoogleOauthConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
