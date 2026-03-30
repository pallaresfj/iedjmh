<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ResolvesPublicContent;
use App\Models\Campus;
use App\Models\MatriculaRequest;
use App\Support\Matricula\MatriculaOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatriculaController extends Controller
{
    use ResolvesPublicContent;

    public function index(): View
    {
        $campuses = collect();

        if ($this->canQueryTable('campuses')) {
            $campuses = Campus::query()
                ->when(
                    $this->canQueryColumn('campuses', 'status'),
                    fn ($query) => $query->where('status', 'published'),
                )
                ->when(
                    $this->canQueryColumn('campuses', 'published_at'),
                    fn ($query) => $query
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now()),
                )
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Campus $campus): array => [
                    'id' => (int) $campus->id,
                    'name' => $campus->name,
                ]);
        }

        return view('public.matricula.index', [
            'title' => 'Formulario de Inscripcion',
            'requirements' => $this->requirements(),
            'gradeOptions' => MatriculaOptions::gradeOptions(),
            'campuses' => $campuses,
            'hasCampuses' => $campuses->isNotEmpty(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canQueryTable('matricula_requests'), 404);

        $campusRule = Rule::exists('campuses', 'id')
            ->where(function ($query): void {
                if ($this->canQueryColumn('campuses', 'status')) {
                    $query->where('status', 'published');
                }

                if ($this->canQueryColumn('campuses', 'published_at')) {
                    $query
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                }
            });

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:80'],
            'grade' => ['required', Rule::in(array_keys(MatriculaOptions::gradeOptions()))],
            'campus_id' => ['required', 'integer', $campusRule],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:1024',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                    $allowedMimeTypes = ['application/pdf', 'application/x-pdf', 'image/jpeg', 'image/png', 'image/webp'];
                    $extension = strtolower((string) $value->getClientOriginalExtension());
                    $clientMime = strtolower((string) $value->getClientMimeType());
                    $detectedMime = strtolower((string) $value->getMimeType());

                    $isAllowedExtension = in_array($extension, $allowedExtensions, true);
                    $isAllowedMime = in_array($clientMime, $allowedMimeTypes, true) || in_array($detectedMime, $allowedMimeTypes, true);

                    if (! $isAllowedExtension && ! $isAllowedMime) {
                        $fail('Cada archivo debe ser PDF, JPG, JPEG, PNG o WEBP.');
                    }
                },
            ],
        ], [
            'attachments.max' => 'Puedes adjuntar maximo 5 archivos.',
            'attachments.*.max' => 'Cada archivo debe pesar maximo 1MB.',
        ]);

        $attachments = collect($request->file('attachments', []))
            ->filter(fn ($file): bool => $file instanceof UploadedFile)
            ->map(function (UploadedFile $file): array {
                $path = $file->store('matricula-attachments', 'local');

                return [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            })
            ->values()
            ->all();

        MatriculaRequest::query()->create([
            'student_name' => $validated['student_name'],
            'document_number' => $validated['document_number'],
            'phone' => $validated['phone'],
            'grade' => $validated['grade'],
            'campus_id' => (int) $validated['campus_id'],
            'attachments' => $attachments !== [] ? $attachments : null,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('matricula.index')
            ->with('matricula_success', 'Solicitud enviada correctamente. Pronto nos pondremos en contacto para continuar el proceso.');
    }

    /**
     * @return Collection<int, array{icon: string, title: string, description: string, emphasized: bool}>
     */
    private function requirements(): Collection
    {
        return collect([
            [
                'icon' => 'id_card',
                'title' => 'Identidad Estudiantil',
                'description' => 'Fotocopia de Registro Civil y Tarjeta de Identidad.',
                'emphasized' => false,
            ],
            [
                'icon' => 'health_and_safety',
                'title' => 'Certificados Académicos',
                'description' => 'Certificados de notas originales de los grados anteriores debidamente aprobados.',
                'emphasized' => false,
            ],
            [
                'icon' => 'description',
                'title' => 'Registro Fotográfico',
                'description' => 'Foto tamaño 3x4 reciente.',
                'emphasized' => false,
            ],
            [
                'icon' => 'shield_person',
                'title' => 'Salud y Seguridad',
                'description' => 'Copia de carnet de EPS o certificado de afiliación vigente (FOSYGA).',
                'emphasized' => false,
            ],
            [
                'icon' => 'family_restroom',
                'title' => 'Documentos Acudiente',
                'description' => 'Fotocopia de documento de identidad de los padres y acudiente. Recibo de servicio público del lugar de residencia.',
                'emphasized' => false,
            ],
            [
                'icon' => 'folder',
                'title' => 'Carpeta de Archivo',
                'description' => 'Toda la documentación debe ser entregada en una carpeta de cartón oficio o cargada digitalmente en el formulario de esta página.',
                'emphasized' => true,
            ],
        ]);
    }
}
