<?php

namespace App\Filament\Resources\AreaPlans\Pages;

use App\Filament\Resources\AreaPlans\AreaPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAreaPlans extends ListRecords
{
    protected static string $resource = AreaPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
