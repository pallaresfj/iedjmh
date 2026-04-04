<?php

namespace App\Filament\Resources\Contracts\Pages\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait HandlesContractValidationFeedback
{
    protected function onValidationError(ValidationException $exception): void
    {
        parent::onValidationError($exception);

        $summary = $this->summarizeValidationErrors($exception);

        if ($summary === null) {
            return;
        }

        Notification::make()
            ->title('No se pudo guardar el contrato')
            ->body($summary)
            ->danger()
            ->send();
    }

    private function summarizeValidationErrors(ValidationException $exception): ?string
    {
        $messages = collect($exception->errors())
            ->flatten()
            ->filter(fn (mixed $message): bool => is_string($message) && trim($message) !== '')
            ->map(fn (string $message): string => trim($message))
            ->unique()
            ->values();

        if ($messages->isEmpty()) {
            return null;
        }

        $previewLimit = 4;
        $preview = $messages->take($previewLimit)->implode('; ');
        $remaining = $messages->count() - min($messages->count(), $previewLimit);

        if ($remaining <= 0) {
            return $preview;
        }

        return "{$preview}; +{$remaining} mas.";
    }
}
