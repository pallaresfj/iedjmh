<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use App\Filament\Resources\Contracts\Pages\Concerns\HandlesContractValidationFeedback;
use App\Models\Contract;
use App\Support\Contracts\ContractPublicationValidator;
use App\Support\Contracts\ContractTimelineValidator;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditContract extends EditRecord
{
    use HandlesContractValidationFeedback;

    protected static string $resource = ContractResource::class;

    protected function beforeSave(): void
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['process_code'] ?? null) && filled($data['fiscal_year'] ?? null)) {
            $data['process_code'] = Contract::nextProcessCode((int) $data['fiscal_year'], (int) $this->record->getKey());
        }

        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
