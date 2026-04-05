<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\MatriculaRequests\MatriculaRequestResource;
use App\Http\Controllers\Controller;
use App\Models\MatriculaRequest;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MatriculaRequestAttachmentController extends Controller
{
    public function __invoke(MatriculaRequest $matriculaRequest, int $attachmentIndex): Response
    {
        abort_unless(MatriculaRequestResource::canView($matriculaRequest), 403);

        $attachments = is_array($matriculaRequest->attachments) ? $matriculaRequest->attachments : [];
        $attachment = $attachments[$attachmentIndex] ?? null;

        if (! is_array($attachment)) {
            abort(404);
        }

        $disk = trim((string) ($attachment['disk'] ?? 'local'));
        $path = trim((string) ($attachment['path'] ?? ''));

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $fileName = trim((string) ($attachment['original_name'] ?? basename($path)));
        $fileName = $fileName !== '' ? $fileName : basename($path);

        return Storage::disk($disk)->response($path, $fileName, [
            'Content-Disposition' => 'inline; filename="'.addslashes($fileName).'"',
        ]);
    }
}
