<?php

namespace App\Filament\Resources\PqrsRequests\Pages;

use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPqrsRequest extends EditRecord
{
    /**
     * @var array<int, string>
     */
    private const CLOSED_STATUSES = ['resolved', 'closed', 'resuelto', 'cerrado', 'finalizado'];

    /**
     * @var array<int, string>
     */
    private const EDITABLE_FIELDS = ['type', 'status', 'priority', 'resolved_at', 'assigned_to', 'internal_notes'];

    protected static string $resource = PqrsRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = array_intersect_key($data, array_flip(self::EDITABLE_FIELDS));
        $data['updated_by'] = auth()->id();

        if (
            in_array((string) ($data['status'] ?? $this->record->status), self::CLOSED_STATUSES, true)
            && blank($data['resolved_at'] ?? $this->record->resolved_at)
        ) {
            $data['resolved_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
