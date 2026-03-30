<?php

namespace App\Filament\Resources\MatriculaRequests\Pages;

use App\Filament\Resources\MatriculaRequests\MatriculaRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMatriculaRequest extends ViewRecord
{
    protected static string $resource = MatriculaRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
