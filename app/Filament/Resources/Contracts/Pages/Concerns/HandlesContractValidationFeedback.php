<?php

namespace App\Filament\Resources\Contracts\Pages\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
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

    /**
     * @param  array<string, string|array<int, string>>  $errors
     */
    protected function reportContractValidationErrors(array $errors): void
    {
        foreach ($errors as $key => $messages) {
            foreach (Arr::wrap($messages) as $message) {
                if (! is_string($message) || trim($message) === '') {
                    continue;
                }

                $message = trim($message);
                $statePath = str_starts_with($key, 'data.') ? $key : "data.{$key}";

                $this->addError($statePath, $message);
                $this->addError($key, $message);
            }
        }

        $summary = $this->summarizeErrorsArray($errors);

        if ($summary === null) {
            return;
        }

        Notification::make()
            ->title('No se pudo guardar el contrato')
            ->body($summary)
            ->danger()
            ->send();
    }

    /**
     * @param  array<string, string|array<int, string>>  $errors
     */
    private function summarizeErrorsArray(array $errors): ?string
    {
        $messages = collect($errors)
            ->flatMap(fn (string|array $error): array => Arr::wrap($error))
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
