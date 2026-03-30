<?php

namespace App\Http\Controllers\Public\Graduates;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Graduate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    use ResolvesPublicContent;

    public function index(Request $request): View|RedirectResponse
    {
        if (Auth::guard('graduate')->check()) {
            return redirect()->route('egresados.panel.resumen');
        }

        $cmsPage = $this->publishedPageByBindingOrSlug('egresados.index', 'egresados');

        return view('public.egresados.auth', [
            'graduationYears' => range((int) now()->format('Y'), 1980),
            'title' => $cmsPage?->title ?: 'Portal de Egresados',
            'lead' => $cmsPage?->summary ?: 'Accede a tus documentos oficiales, actualiza tus datos y mantente conectado con la red institucional de egresados.',
            'banner' => $this->resolvePageBanner($cmsPage),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::guard('graduate')->check()) {
            return redirect()->route('egresados.panel.resumen');
        }

        $validated = $request->validate([
            'national_id' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $nationalId = trim((string) $validated['national_id']);
        $graduate = Graduate::query()->where('national_id', $nationalId)->first();

        if ($graduate === null) {
            return back()
                ->withInput($request->only('national_id'))
                ->withErrors([
                    'login' => 'No encontramos un egresado con ese numero de identificacion.',
                ]);
        }

        if ($graduate->status === 'blocked') {
            return back()
                ->withInput($request->only('national_id'))
                ->withErrors([
                    'login' => 'Tu cuenta se encuentra bloqueada. Contacta a la institucion para soporte.',
                ]);
        }

        if (! $graduate->isActive()) {
            return back()
                ->withInput($request->only('national_id'))
                ->withErrors([
                    'login' => 'Debes completar primero tu preregistro para activar el acceso.',
                ]);
        }

        $remember = (bool) ($validated['remember'] ?? false);
        $attempted = Auth::guard('graduate')->attempt([
            'national_id' => $nationalId,
            'password' => $validated['password'],
            'status' => 'active',
        ], $remember);

        if (! $attempted) {
            return back()
                ->withInput($request->only('national_id'))
                ->withErrors([
                    'login' => 'Credenciales invalidas. Verifica tu documento y contraseña.',
                ]);
        }

        $request->session()->regenerate();

        $graduate->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->intended(route('egresados.panel.resumen'));
    }

    public function preregister(Request $request): RedirectResponse
    {
        if (Auth::guard('graduate')->check()) {
            return redirect()->route('egresados.panel.resumen');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:80'],
            'graduation_year' => ['required', 'integer', 'between:1980,'.((int) now()->format('Y') + 1)],
            'national_id' => ['required', 'string', 'max:80'],
            'current_occupation' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'data_processing_consent' => ['accepted'],
        ], [
            'data_processing_consent.accepted' => 'Debes aceptar el consentimiento de tratamiento de datos.',
        ]);

        $graduate = Graduate::query()
            ->where('national_id', trim((string) $validated['national_id']))
            ->first();

        if ($graduate === null) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'preregistro' => 'No existe un registro institucional para ese numero de identificacion.',
                ]);
        }

        if ($graduate->status === 'blocked') {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'preregistro' => 'Este registro se encuentra bloqueado. Comunicate con la institucion.',
                ]);
        }

        $emailConflict = Graduate::query()
            ->where('email', $validated['email'])
            ->whereKeyNot($graduate->id)
            ->exists();

        if ($emailConflict) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Ese correo ya esta asociado a otro egresado.',
                ]);
        }

        $graduate->fill([
            'full_name' => trim((string) $validated['full_name']),
            'email' => trim((string) $validated['email']),
            'phone' => trim((string) $validated['phone']),
            'graduation_year' => (int) $validated['graduation_year'],
            'current_occupation' => trim((string) $validated['current_occupation']),
            'city' => trim((string) $validated['city']),
            'country' => trim((string) $validated['country']),
            'password' => Hash::make($validated['password']),
            'data_processing_consent_at' => now(),
            'status' => 'active',
            'activated_at' => $graduate->activated_at ?: now(),
        ]);
        $graduate->save();

        Auth::guard('graduate')->login($graduate, true);
        $request->session()->regenerate();

        return redirect()
            ->route('egresados.panel.resumen')
            ->with('egresados_status', 'Tu cuenta fue activada correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('graduate')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('egresados.index')
            ->with('egresados_status', 'Sesion cerrada correctamente.');
    }
}
