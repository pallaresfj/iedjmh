<?php

namespace App\Filament\Resources\MatriculaRequests\Pages;

use App\Filament\Resources\MatriculaRequests\MatriculaRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditMatriculaRequest extends EditRecord
{
    /**
     * @var array<int, string>
     */
    private const EDITABLE_FIELDS = ['status', 'reviewed_at', 'internal_notes'];

    protected static string $resource = MatriculaRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = array_intersect_key($data, array_flip(self::EDITABLE_FIELDS));

        if (($data['status'] ?? $this->record->status) !== 'pending' && blank($data['reviewed_at'] ?? $this->record->reviewed_at)) {
            $data['reviewed_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
