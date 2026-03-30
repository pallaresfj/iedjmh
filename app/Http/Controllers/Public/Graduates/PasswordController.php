<?php

namespace App\Http\Controllers\Public\Graduates;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Notifications\GraduateResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('graduate')->check()) {
            return redirect()->route('egresados.panel.resumen');
        }

        return view('public.egresados.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $graduate = Graduate::query()
            ->where('email', trim((string) $validated['email']))
            ->where('status', 'active')
            ->first();

        if ($graduate !== null) {
            $token = Password::broker('graduates')->createToken($graduate);
            $graduate->notify(new GraduateResetPasswordNotification($token));
        }

        return back()->with('status', 'Si el correo existe, recibiras un enlace para restablecer tu contraseña.');
    }

    public function edit(Request $request, string $token): View|RedirectResponse
    {
        if (Auth::guard('graduate')->check()) {
            return redirect()->route('egresados.panel.resumen');
        }

        return view('public.egresados.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker('graduates')->reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $request->string('password_confirmation')->toString(),
                'token' => $validated['token'],
            ],
            function (Graduate $graduate, string $password): void {
                $graduate->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($graduate));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('egresados.index')
                ->with('egresados_status', 'Tu contraseña fue actualizada correctamente.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
