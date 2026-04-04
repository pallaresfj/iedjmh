<?php

namespace App\Http\Controllers\Public\Graduates;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Graduate;
use App\Models\GraduateDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

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
            return redirect()
                ->route('egresados.index')
                ->withInput($request->only('national_id'))
                ->withErrors([
                    'login' => 'No encontramos un egresado con ese numero de identificacion.',
                ]);
        }

        if ($graduate->status === 'blocked') {
            return redirect()
                ->route('egresados.index')
                ->withInput($request->only('national_id'))
                ->withErrors([
                    'login' => 'Tu cuenta se encuentra bloqueada. Contacta a la institucion para soporte.',
                ]);
        }

        if (! $graduate->isActive()) {
            return redirect()
                ->route('egresados.index')
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
            return redirect()
                ->route('egresados.index')
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
            'identity_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:1024'],
            'data_processing_consent' => ['accepted'],
        ], [
            'data_processing_consent.accepted' => 'Debes aceptar el consentimiento de tratamiento de datos.',
        ]);

        $graduate = Graduate::query()
            ->where('national_id', trim((string) $validated['national_id']))
            ->first();

        if ($graduate === null) {
            return redirect()
                ->route('egresados.index')
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'preregistro' => 'No existe un registro institucional para ese numero de identificacion.',
                ]);
        }

        if ($graduate->status === 'blocked') {
            return redirect()
                ->route('egresados.index')
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
            return redirect()
                ->route('egresados.index')
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Ese correo ya esta asociado a otro egresado.',
                ]);
        }

        $identityDocumentPath = $request->file('identity_document')->store(
            'graduates/'.$graduate->id.'/identity-documents',
            'local'
        );

        $previousIdentityPath = null;

        try {
            DB::transaction(function () use ($graduate, $validated, $identityDocumentPath, &$previousIdentityPath): void {
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

                $identityRecord = $graduate->documents()
                    ->where('title', 'Identificación')
                    ->where('type_label', 'Personal')
                    ->first();

                if ($identityRecord !== null && $identityRecord->file_disk === 'local' && filled($identityRecord->file_path)) {
                    $previousIdentityPath = $identityRecord->file_path;
                }

                if ($identityRecord === null) {
                    $identityRecord = new GraduateDocument;
                    $identityRecord->graduate_id = $graduate->id;
                    $identityRecord->sort_order = 0;
                }

                $identityRecord->fill([
                    'title' => 'Identificación',
                    'type_label' => 'Personal',
                    'description' => 'Documento de identidad del egresado',
                    'drive_url' => null,
                    'file_path' => $identityDocumentPath,
                    'file_disk' => 'local',
                    'is_official' => true,
                    'is_visible' => true,
                ]);
                $identityRecord->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($identityDocumentPath);

            throw $exception;
        }

        if (filled($previousIdentityPath) && $previousIdentityPath !== $identityDocumentPath) {
            Storage::disk('local')->delete($previousIdentityPath);
        }

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
