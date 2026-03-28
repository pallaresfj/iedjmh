<?php

namespace App\Filament\Resources\PqrsRequests\Pages;

use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use App\Support\Pqrs\TrackingCodeGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreatePqrsRequest extends CreateRecord
{
    /**
     * @var array<int, string>
     */
    private const CLOSED_STATUSES = ['resolved', 'closed', 'resuelto', 'cerrado', 'finalizado'];

    protected static string $resource = PqrsRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tracking_code'] = app(TrackingCodeGenerator::class)->generate();
        $data['submitted_at'] = $data['submitted_at'] ?? now();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if (in_array((string) ($data['status'] ?? ''), self::CLOSED_STATUSES, true) && blank($data['resolved_at'] ?? null)) {
            $data['resolved_at'] = now();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
