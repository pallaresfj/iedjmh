<?php

namespace App\Filament\Resources\ContractTypes\Pages;

use App\Filament\Resources\ContractTypes\ContractTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContractType extends CreateRecord
{
    protected static string $resource = ContractTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
