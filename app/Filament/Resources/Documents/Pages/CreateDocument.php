<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        if (blank($data['document_number'] ?? null)) {
            $data['document_number'] = static::nextDocumentNumber();
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private static function nextDocumentNumber(): string
    {
        $maxConsecutive = Document::query()
            ->whereNotNull('document_number')
            ->pluck('document_number')
            ->reduce(function (int $max, mixed $value): int {
                $number = trim((string) $value);

                if (preg_match('/^DOC\\s*-\\s*(\\d+)$/i', $number, $matches) !== 1) {
                    return $max;
                }

                return max($max, (int) $matches[1]);
            }, 0);

        return sprintf('DOC - %04d', $maxConsecutive + 1);
    }
}
