<?php

namespace App\Http\Controllers\Public\Graduates;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\GraduateDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PanelController extends Controller
{
    public function summary(Request $request): View
    {
        /** @var Graduate $graduate */
        $graduate = $request->user('graduate');

        $documents = $this->visibleDocuments($graduate);

        return $this->panelView($graduate, 'resumen', [
            'documents' => $documents,
        ]);
    }

    public function certificates(Request $request): View
    {
        /** @var Graduate $graduate */
        $graduate = $request->user('graduate');

        return $this->panelView($graduate, 'mis-documentos', [
            'documents' => $this->visibleDocuments($graduate),
        ]);
    }

    public function showDocumentFile(Request $request, GraduateDocument $document): Response
    {
        /** @var Graduate $graduate */
        $graduate = $request->user('graduate');

        if (
            $document->graduate_id !== $graduate->id
            || ! $document->is_visible
            || blank($document->file_path)
        ) {
            abort(404);
        }

        $disk = trim((string) ($document->file_disk ?: 'local'));
        $path = trim((string) $document->file_path);

        if (! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $fileName = basename($path);

        return Storage::disk($disk)->response($path, $fileName, [
            'Content-Disposition' => 'inline; filename="'.addslashes($fileName).'"',
        ]);
    }

    public function academicRecord(Request $request): View
    {
        /** @var Graduate $graduate */
        $graduate = $request->user('graduate');

        return $this->panelView($graduate, 'registro-academico');
    }

    public function settings(Request $request): View
    {
        /** @var Graduate $graduate */
        $graduate = $request->user('graduate');

        return $this->panelView($graduate, 'configuracion');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        /** @var Graduate $graduate */
        $graduate = $request->user('graduate');

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('graduates', 'email')->ignore($graduate->id)],
            'phone' => ['required', 'string', 'max:80'],
            'current_occupation' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'data_processing_consent' => ['accepted'],
        ], [
            'data_processing_consent.accepted' => 'Debes aceptar el consentimiento para guardar cambios.',
        ]);

        $graduate->fill([
            'full_name' => trim((string) $validated['full_name']),
            'email' => trim((string) $validated['email']),
            'phone' => trim((string) $validated['phone']),
            'current_occupation' => trim((string) $validated['current_occupation']),
            'city' => trim((string) $validated['city']),
            'country' => trim((string) $validated['country']),
            'data_processing_consent_at' => now(),
        ]);
        $graduate->save();

        return redirect()
            ->route('egresados.panel.configuracion')
            ->with('egresados_status', 'Tu perfil fue actualizado correctamente.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function panelView(Graduate $graduate, string $section, array $extra = []): View
    {
        return view('public.egresados.panel', array_merge([
            'graduate' => $graduate,
            'section' => $section,
        ], $extra));
    }

    /**
     * @return Collection<int, GraduateDocument>
     */
    private function visibleDocuments(Graduate $graduate): Collection
    {
        return $graduate->documents()
            ->where('is_visible', true)
            ->get();
    }
}
