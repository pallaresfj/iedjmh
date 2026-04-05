<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\Graduates\GraduateResource;
use App\Http\Controllers\Controller;
use App\Models\GraduateDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class GraduateDocumentFileController extends Controller
{
    public function __invoke(GraduateDocument $document): Response
    {
        abort_unless(GraduateResource::canView($document->graduate), 403);

        $disk = trim((string) ($document->file_disk ?: 'local'));
        $path = trim((string) ($document->file_path ?? ''));

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $fileName = basename($path);

        return Storage::disk($disk)->response($path, $fileName, [
            'Content-Disposition' => 'inline; filename="'.addslashes($fileName).'"',
        ]);
    }
}
