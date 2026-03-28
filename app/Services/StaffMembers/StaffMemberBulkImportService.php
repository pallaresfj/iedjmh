<?php

namespace App\Services\StaffMembers;

use App\Models\Campus;
use App\Models\StaffMember;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class StaffMemberBulkImportService
{
    /**
     * @var array<int, string>
     */
    public const TEMPLATE_HEADERS = [
        'Nombre completo *',
        'Cargo *',
        'Dependencia',
        'Tipo de personal',
        'Sede (slug)',
        'Correo institucional',
        'Telefono',
        'Estado',
        'Fecha de publicacion',
        'Orden',
    ];

    /**
     * @var array<int, array<int, string|int>>
     */
    private const TEMPLATE_SAMPLE_ROWS = [
        [
            'Ana Lucia Martinez',
            'Rectora',
            'Rectoria',
            'directive (directive|teacher|administrative|support)',
            'sede-principal',
            'ana.martinez@iedjmh.edu.co',
            '3010001111',
            'published (draft|published|archived)',
            '2026-03-28 08:00:00',
            1,
        ],
        [
            'Carlos Andres Perez',
            'Docente de Matematicas',
            'Academico',
            'teacher (directive|teacher|administrative|support)',
            'sede-campestre',
            'carlos.perez@iedjmh.edu.co',
            '3020002222',
            'draft (draft|published|archived)',
            '',
            2,
        ],
    ];

    /**
     * @var array<int, string>
     */
    private const CANONICAL_FIELDS = [
        'full_name',
        'position_title',
        'department_label',
        'staff_group',
        'campus_slug',
        'institutional_email',
        'phone',
        'status',
        'published_at',
        'sort_order',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const HEADER_ALIASES = [
        'full_name' => ['full_name', 'nombre_completo', 'nombre'],
        'position_title' => ['position_title', 'cargo'],
        'department_label' => ['department_label', 'dependencia', 'dependencia_etiqueta'],
        'staff_group' => ['staff_group', 'tipo_de_personal', 'grupo'],
        'campus_slug' => ['campus_slug', 'sede_slug', 'sede'],
        'institutional_email' => ['institutional_email', 'correo_institucional', 'email_institucional'],
        'phone' => ['phone', 'telefono'],
        'status' => ['status', 'estado'],
        'published_at' => ['published_at', 'fecha_de_publicacion'],
        'sort_order' => ['sort_order', 'orden'],
    ];

    public function import(UploadedFile $file, int $userId): StaffMemberBulkImportResult
    {
        $result = new StaffMemberBulkImportResult;
        [$sheetName, $rows] = $this->extractRowsFromWorkbook($file);
        $result->setSourceContext($sheetName, count($rows));

        foreach ($rows as $row) {
            $rowNumber = $row['row_number'];
            $rowData = $this->normalizeRow($row['data']);

            if ($this->isEmptyRow($rowData)) {
                $result->incrementSkipped();

                continue;
            }

            try {
                $validated = $this->validateRow($rowData);
                $this->persistRow($validated, $userId, $result);
            } catch (ValidationException $exception) {
                $messages = collect($exception->errors())
                    ->flatten()
                    ->implode('; ');

                $result->addFailure($rowNumber, $messages);
            } catch (Throwable $exception) {
                $result->addFailure($rowNumber, 'Error inesperado durante la importacion.');
            }
        }

        return $result;
    }

    public static function templateXlsx(): string
    {
        return Excel::raw(new class(self::TEMPLATE_HEADERS, self::TEMPLATE_SAMPLE_ROWS) implements FromArray
        {
            /**
             * @param  array<int, string>  $headers
             * @param  array<int, array<int, string|int>>  $sampleRows
             */
            public function __construct(private array $headers, private array $sampleRows) {}

            public function array(): array
            {
                return [
                    $this->headers,
                    ...$this->sampleRows,
                ];
            }
        }, ExcelWriter::XLSX);
    }

    /**
     * @return array{0: string, 1: array<int, array{row_number: int, data: array<string, mixed>}>}
     */
    protected function extractRowsFromWorkbook(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => 'No se pudo abrir el archivo de importacion.',
            ]);
        }

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $rows = $this->extractRowsFromWorksheet($worksheet);

            if ($rows !== null) {
                $sheetName = trim((string) $worksheet->getTitle()) ?: 'Hoja sin nombre';
                $spreadsheet->disconnectWorksheets();

                return [$sheetName, $rows];
            }
        }

        $spreadsheet->disconnectWorksheets();

        throw ValidationException::withMessages([
            'file' => 'No se encontro una hoja con encabezados validos. Debe incluir "Nombre completo *" y "Cargo *".',
        ]);
    }

    /**
     * @return array<int, array{row_number: int, data: array<string, mixed>}>|null
     */
    protected function extractRowsFromWorksheet(Worksheet $worksheet): ?array
    {
        $highestRow = (int) $worksheet->getHighestDataRow();

        if ($highestRow < 2) {
            return null;
        }

        $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestDataColumn());
        $maxHeaderScanRows = min($highestRow, 10);

        for ($headerRow = 1; $headerRow <= $maxHeaderScanRows; $headerRow++) {
            $headerMap = $this->extractCanonicalHeaderMap($worksheet, $headerRow, $highestColumnIndex);

            if (! $this->headerMapHasRequiredFields($headerMap)) {
                continue;
            }

            $rows = [];

            for ($dataRow = $headerRow + 1; $dataRow <= $highestRow; $dataRow++) {
                $rowData = [];

                foreach ($headerMap as $column => $field) {
                    $rowData[$field] = $this->readWorksheetCellValue($worksheet, $column, $dataRow);
                }

                $rows[] = [
                    'row_number' => $dataRow,
                    'data' => $rowData,
                ];
            }

            return $rows;
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function extractCanonicalHeaderMap(Worksheet $worksheet, int $headerRow, int $highestColumnIndex): array
    {
        $headerMap = [];

        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $value = $this->readWorksheetCellValue($worksheet, $column, $headerRow);
            $normalizedHeader = $this->normalizeHeaderName($value);

            if (blank($normalizedHeader)) {
                continue;
            }

            $canonicalField = $this->resolveCanonicalFieldForHeader($normalizedHeader);

            if (blank($canonicalField)) {
                continue;
            }

            $headerMap[$column] = $canonicalField;
        }

        return $headerMap;
    }

    /**
     * @param  array<int, string>  $headerMap
     */
    protected function headerMapHasRequiredFields(array $headerMap): bool
    {
        $fields = array_values(array_unique($headerMap));

        return in_array('full_name', $fields, true)
            && in_array('position_title', $fields, true);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function validateRow(array $row): array
    {
        $data = [
            'full_name' => $this->toString($row['full_name'] ?? null),
            'position_title' => $this->toString($row['position_title'] ?? null),
            'department_label' => $this->toNullableString($row['department_label'] ?? null),
            'staff_group' => $this->toNullableString($row['staff_group'] ?? null) ?: 'directive',
            'campus_slug' => $this->toNullableString($row['campus_slug'] ?? null),
            'institutional_email' => $this->toNullableString($row['institutional_email'] ?? null),
            'phone' => $this->toNullableString($row['phone'] ?? null),
            'status' => $this->toNullableString($row['status'] ?? null) ?: 'draft',
            'published_at' => $this->normalizePublishedAt($row['published_at'] ?? null),
            'sort_order' => $this->toNullableString($row['sort_order'] ?? null) ?: 0,
        ];

        $validated = Validator::make(
            $data,
            [
                'full_name' => ['required', 'string', 'max:255'],
                'position_title' => ['required', 'string', 'max:255'],
                'department_label' => ['nullable', 'string', 'max:255'],
                'staff_group' => ['required', Rule::in(array_keys(StaffMember::STAFF_GROUP_OPTIONS))],
                'campus_slug' => [
                    'nullable',
                    'string',
                    Rule::exists('campuses', 'slug')->where(fn ($query) => $query->whereNull('deleted_at')),
                ],
                'institutional_email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'status' => ['required', Rule::in(array_keys(StaffMember::STATUS_OPTIONS))],
                'published_at' => ['nullable', 'date'],
                'sort_order' => ['required', 'integer', 'min:0'],
            ],
            [],
            [
                'full_name' => 'nombre completo',
                'position_title' => 'cargo',
                'department_label' => 'dependencia',
                'staff_group' => 'tipo de personal',
                'campus_slug' => 'sede (slug)',
                'institutional_email' => 'correo institucional',
                'phone' => 'telefono',
                'published_at' => 'fecha de publicacion',
                'sort_order' => 'orden',
            ],
        )->validate();

        $validated['institutional_email'] = filled($validated['institutional_email'] ?? null)
            ? Str::lower((string) $validated['institutional_email'])
            : null;

        $validated['sort_order'] = (int) $validated['sort_order'];

        $validated['campus_id'] = filled($validated['campus_slug'] ?? null)
            ? Campus::query()->where('slug', $validated['campus_slug'])->value('id')
            : null;

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function persistRow(array $validated, int $userId, StaffMemberBulkImportResult $result): void
    {
        $record = $this->resolveRecordByInstitutionalEmail($validated['institutional_email'] ?? null) ?? new StaffMember;

        $record->fill([
            'full_name' => $validated['full_name'],
            'position_title' => $validated['position_title'],
            'department_label' => $validated['department_label'],
            'staff_group' => $validated['staff_group'],
            'campus_id' => $validated['campus_id'],
            'institutional_email' => $validated['institutional_email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'],
            'sort_order' => $validated['sort_order'],
        ]);

        if ($record->status === 'published' && blank($record->published_at)) {
            $record->published_at = now();
        }

        if (! $record->exists) {
            $record->created_by = $userId;
        }

        $record->updated_by = $userId;
        $record->save();

        if ($record->wasRecentlyCreated) {
            $result->incrementCreated();

            return;
        }

        $result->incrementUpdated();
    }

    protected function resolveRecordByInstitutionalEmail(?string $email): ?StaffMember
    {
        if (blank($email)) {
            return null;
        }

        return StaffMember::query()
            ->whereRaw('LOWER(institutional_email) = ?', [Str::lower($email)])
            ->first();
    }

    protected function readWorksheetCellValue(Worksheet $worksheet, int $column, int $row): mixed
    {
        $cellCoordinate = Coordinate::stringFromColumnIndex($column).$row;
        $cell = $worksheet->getCell($cellCoordinate);
        $value = $cell->getValue();

        if (! (is_string($value) && str_starts_with($value, '='))) {
            return $value;
        }

        try {
            $calculated = $cell->getCalculatedValue();

            return $calculated !== null ? $calculated : $value;
        } catch (Throwable $exception) {
            return $value;
        }
    }

    protected function normalizeHeaderName(mixed $value): ?string
    {
        $state = trim((string) $value);

        if ($state === '') {
            return null;
        }

        return Str::of($state)
            ->ascii()
            ->lower()
            ->replace('*', '')
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    protected function resolveCanonicalFieldForHeader(string $normalizedHeader): ?string
    {
        foreach (self::HEADER_ALIASES as $field => $aliases) {
            if (in_array($normalizedHeader, $aliases, true)) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach (self::HEADER_ALIASES as $field => $aliases) {
            $normalized[$field] = $this->firstMatchedValue($row, $aliases);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $aliases
     */
    protected function firstMatchedValue(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row)) {
                return $row[$alias];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach (self::CANONICAL_FIELDS as $field) {
            if (filled($this->toNullableString($row[$field] ?? null))) {
                return false;
            }
        }

        return true;
    }

    protected function toString(mixed $value): string
    {
        return trim((string) $value);
    }

    protected function toNullableString(mixed $value): ?string
    {
        $state = trim((string) $value);

        return $state === '' ? null : $state;
    }

    protected function normalizePublishedAt(mixed $value): mixed
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
        }

        if (is_string($value)) {
            try {
                return Carbon::parse(trim($value));
            } catch (Throwable $exception) {
                throw ValidationException::withMessages([
                    'published_at' => 'La fecha de publicacion no es valida.',
                ]);
            }
        }

        return $value;
    }
}
