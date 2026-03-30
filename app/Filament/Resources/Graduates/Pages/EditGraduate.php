<?php

namespace App\Filament\Resources\Graduates\Pages;

use App\Filament\Resources\Graduates\GraduateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGraduate extends EditRecord
{
    protected static string $resource = GraduateResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
