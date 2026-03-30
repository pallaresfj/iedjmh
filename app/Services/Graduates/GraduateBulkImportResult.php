<?php

namespace App\Services\Graduates;

class GraduateBulkImportResult
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /**
     * @var array<int, array{row: int, message: string}>
     */
    public array $failures = [];

    public ?string $sheetName = null;

    public int $totalRows = 0;

    public function incrementCreated(): void
    {
        $this->created++;
    }

    public function incrementUpdated(): void
    {
        $this->updated++;
    }

    public function incrementSkipped(): void
    {
        $this->skipped++;
    }

    public function addFailure(int $row, string $message): void
    {
        $this->failures[] = [
            'row' => $row,
            'message' => $message,
        ];
    }

    public function hasFailures(): bool
    {
        return $this->failures !== [];
    }

    public function setSourceContext(string $sheetName, int $totalRows): void
    {
        $this->sheetName = $sheetName;
        $this->totalRows = $totalRows;
    }

    public function getNotificationBody(): string
    {
        $parts = [
            "Hoja: {$this->sheetName}",
            "Filas leidas: {$this->totalRows}",
            "Creados: {$this->created}",
            "Actualizados: {$this->updated}",
            "Omitidos: {$this->skipped}",
        ];

        if ($this->hasFailures()) {
            $parts[] = 'Errores: '.count($this->failures);
            $parts[] = 'Detalle: '.$this->firstFailuresSummary();
        }

        return implode(' | ', array_filter($parts));
    }

    private function firstFailuresSummary(): string
    {
        return collect($this->failures)
            ->take(3)
            ->map(fn (array $failure): string => "Fila {$failure['row']}: {$failure['message']}")
            ->implode(' | ');
    }
}

