<?php

namespace App\Services\StaffMembers;

class StaffMemberBulkImportResult
{
    public ?string $sourceSheet = null;

    public int $analyzedRows = 0;

    public int $created = 0;

    public int $updated = 0;

    public int $failed = 0;

    public int $skipped = 0;

    /**
     * @var array<int, array{row: int, message: string}>
     */
    public array $errors = [];

    public function setSourceContext(string $sourceSheet, int $analyzedRows): void
    {
        $this->sourceSheet = $sourceSheet;
        $this->analyzedRows = $analyzedRows;
    }

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
        $this->failed++;

        $this->errors[] = [
            'row' => $row,
            'message' => $message,
        ];
    }

    public function hasFailures(): bool
    {
        return $this->failed > 0;
    }

    public function getSummaryLine(): string
    {
        $context = filled($this->sourceSheet)
            ? "Hoja: {$this->sourceSheet}. Filas analizadas: {$this->analyzedRows}. "
            : '';

        $omitted = "Omitidos: {$this->skipped}";

        if ($this->skipped > 0) {
            $omitted .= ' (filas vacias).';
        } else {
            $omitted .= '.';
        }

        return "{$context}Creados: {$this->created}. Actualizados: {$this->updated}. Fallidos: {$this->failed}. {$omitted}";
    }

    public function getNotificationBody(int $maxErrors = 5): string
    {
        if (! $this->hasFailures()) {
            if (($this->created + $this->updated) === 0 && $this->skipped > 0) {
                return $this->getSummaryLine()."\n\nNo se detectaron filas con datos importables en las columnas reconocidas.";
            }

            return $this->getSummaryLine();
        }

        $lines = [$this->getSummaryLine(), '', 'Errores:'];

        foreach (array_slice($this->errors, 0, $maxErrors) as $error) {
            $lines[] = "- Fila {$error['row']}: {$error['message']}";
        }

        $remaining = count($this->errors) - $maxErrors;

        if ($remaining > 0) {
            $lines[] = "- ... y {$remaining} error(es) adicional(es).";
        }

        return implode("\n", $lines);
    }
}
