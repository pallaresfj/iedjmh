<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use App\Filament\Resources\Contracts\Pages\Concerns\HandlesContractValidationFeedback;
use App\Models\Contract;
use App\Support\Contracts\ContractPublicationValidator;
use App\Support\Contracts\ContractTimelineValidator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateContract extends CreateRecord
{
    use HandlesContractValidationFeedback;

    protected static string $resource = ContractResource::class;

    protected function beforeCreate(): void
    {
        $errors = array_merge(
            ContractTimelineValidator::validate($this->data),
            ContractPublicationValidator::validate($this->data),
        );

        if ($errors !== []) {
            $exception = ValidationException::withMessages($errors);
            $this->onValidationError($exception);

            throw $exception;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['process_code'] ?? null) && filled($data['fiscal_year'] ?? null)) {
            $data['process_code'] = Contract::nextProcessCode((int) $data['fiscal_year']);
        }

        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
