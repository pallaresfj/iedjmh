<?php

namespace App\Filament\Resources\AreaPlans\Pages;

use App\Filament\Resources\AreaPlans\AreaPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAreaPlan extends CreateRecord
{
    protected static string $resource = AreaPlanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
