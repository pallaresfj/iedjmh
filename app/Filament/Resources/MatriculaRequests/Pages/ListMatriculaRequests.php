<?php

namespace App\Filament\Resources\MatriculaRequests\Pages;

use App\Filament\Resources\MatriculaRequests\MatriculaRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListMatriculaRequests extends ListRecords
{
    protected static string $resource = MatriculaRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
