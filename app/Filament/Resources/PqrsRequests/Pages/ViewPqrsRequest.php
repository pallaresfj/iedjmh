<?php

namespace App\Filament\Resources\PqrsRequests\Pages;

use App\Filament\Resources\PqrsRequests\PqrsRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPqrsRequest extends ViewRecord
{
    protected static string $resource = PqrsRequestResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
