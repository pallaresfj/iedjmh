<?php

namespace App\Services\Graduates;

use App\Models\Graduate;
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

class GraduateBulkImportService
{
    /**
     * @var array<int, string>
     */
    public const TEMPLATE_HEADERS = [
        'Identificacion nacional *',
        'Nombre completo *',
        'Ano de graduacion *',
        'Correo',
        'Telefono',
        'Ocupacion actual',
        'Ciudad',
        'Pais',
        'Estado',
        'Titulo academico',
        'Fecha de grado',
        'Acta',
        'Folio',
        'Verificacion',
    ];

    /**
     * @var array<int, array<int, string|int>>
     */
    private const TEMPLATE_SAMPLE_ROWS = [
        [
            '1234567890',
            'Mateo Rivera',
            2023,
            'm.rivera@correo.com',
            '3000000000',
            'Especialista en Sistemas de Riego',
            'Pivijay',
            'Colombia',
            'preloaded (preloaded|active|blocked)',
            'Tecnico Agropecuario',
            '2023-12-15',
            'ACT-2023-115',
            'FOL-903',
            'verified (pending|verified)',
        ],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const HEADER_ALIASES = [
        'national_id' => ['national_id', 'identificacion_nacional', 'documento', 'cedula'],
        'full_name' => ['full_name', 'nombre_completo', 'nombre'],
        'graduation_year' => ['graduation_year', 'ano_de_graduacion', 'year', 'promocion'],
        'email' => ['email', 'correo', 'correo_electronico'],
        'phone' => ['phone', 'telefono', 'celular'],
        'current_occupation' => ['current_occupation', 'ocupacion_actual', 'ocupacion'],
        'city' => ['city', 'ciudad'],
        'country' => ['country', 'pais'],
        'status' => ['status', 'estado'],
        'academic_title' => ['academic_title', 'titulo_academico', 'titulo'],
        'graduation_date' => ['graduation_date', 'fecha_de_grado'],
        'graduation_act_number' => ['graduation_act_number', 'acta'],
        'graduation_folio' => ['graduation_folio', 'folio'],
        'record_verification_status' => ['record_verification_status', 'verificacion', 'estado_verificacion'],
    ];

    public function import(UploadedFile $file): GraduateBulkImportResult
    {
        $result = new GraduateBulkImportResult;
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
                $this->persistRow($validated, $result);
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
            'file' => 'No se encontro una hoja con encabezados validos. Debe incluir "Identificacion nacional *".',
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
        return in_array('national_id', $headerMap, true);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function validateRow(array $row): array
    {
        $nationalId = $this->toString($row['national_id'] ?? null);
        $existing = Graduate::query()->where('national_id', $nationalId)->first();

        $data = [
            'national_id' => $nationalId,
            'full_name' => $this->toString($row['full_name'] ?? null),
            'graduation_year' => (int) ($this->toNullableString($row['graduation_year'] ?? null) ?: 0),
            'email' => $this->toNullableString($row['email'] ?? null),
            'phone' => $this->toNullableString($row['phone'] ?? null),
            'current_occupation' => $this->toNullableString($row['current_occupation'] ?? null),
            'city' => $this->toNullableString($row['city'] ?? null),
            'country' => $this->toNullableString($row['country'] ?? null),
            'status' => $this->toNullableString($row['status'] ?? null) ?: 'preloaded',
            'academic_title' => $this->toNullableString($row['academic_title'] ?? null),
            'graduation_date' => $this->normalizeDateValue($row['graduation_date'] ?? null),
            'graduation_act_number' => $this->toNullableString($row['graduation_act_number'] ?? null),
            'graduation_folio' => $this->toNullableString($row['graduation_folio'] ?? null),
            'record_verification_status' => $this->toNullableString($row['record_verification_status'] ?? null) ?: 'pending',
        ];

        return Validator::make(
            $data,
            [
                'national_id' => ['required', 'string', 'max:80'],
                'full_name' => ['required', 'string', 'max:255'],
                'graduation_year' => ['required', 'integer', 'between:1980,'.((int) now()->format('Y') + 1)],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('graduates', 'email')->ignore($existing?->id)],
                'phone' => ['nullable', 'string', 'max:80'],
                'current_occupation' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'country' => ['nullable', 'string', 'max:255'],
                'status' => ['required', Rule::in(array_keys(Graduate::STATUS_OPTIONS))],
                'academic_title' => ['nullable', 'string', 'max:255'],
                'graduation_date' => ['nullable', 'date'],
                'graduation_act_number' => ['nullable', 'string', 'max:120'],
                'graduation_folio' => ['nullable', 'string', 'max:120'],
                'record_verification_status' => ['required', Rule::in(array_keys(Graduate::VERIFICATION_STATUS_OPTIONS))],
            ],
            [],
            [
                'national_id' => 'identificacion nacional',
                'full_name' => 'nombre completo',
                'graduation_year' => 'ano de graduacion',
                'email' => 'correo',
                'phone' => 'telefono',
                'current_occupation' => 'ocupacion actual',
                'city' => 'ciudad',
                'country' => 'pais',
                'status' => 'estado',
                'academic_title' => 'titulo academico',
                'graduation_date' => 'fecha de grado',
                'graduation_act_number' => 'acta',
                'graduation_folio' => 'folio',
                'record_verification_status' => 'verificacion',
            ],
        )->validate();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function persistRow(array $validated, GraduateBulkImportResult $result): void
    {
        $graduate = Graduate::query()->firstOrNew([
            'national_id' => $validated['national_id'],
        ]);

        $wasExisting = $graduate->exists;

        $graduate->fill([
            'full_name' => $validated['full_name'],
            'graduation_year' => $validated['graduation_year'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'current_occupation' => $validated['current_occupation'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'status' => $validated['status'],
            'academic_title' => $validated['academic_title'],
            'graduation_date' => $validated['graduation_date'],
            'graduation_act_number' => $validated['graduation_act_number'],
            'graduation_folio' => $validated['graduation_folio'],
            'record_verification_status' => $validated['record_verification_status'],
        ]);

        if ($graduate->status === 'active' && $graduate->activated_at === null) {
            $graduate->activated_at = now();
        }

        $graduate->save();

        if ($wasExisting) {
            $result->incrementUpdated();
        } else {
            $result->incrementCreated();
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row): array
    {
        return collect($row)
            ->mapWithKeys(fn ($value, $key): array => [Str::snake((string) $key) => $value])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        return collect($row)->every(function (mixed $value): bool {
            if (is_string($value)) {
                return trim($value) === '';
            }

            return $value === null;
        });
    }

    protected function readWorksheetCellValue(Worksheet $worksheet, int $column, int $row): mixed
    {
        $value = $worksheet->getCellByColumnAndRow($column, $row)->getValue();

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    protected function normalizeHeaderName(mixed $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->replace('*', '')
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();
    }

    protected function resolveCanonicalFieldForHeader(string $header): ?string
    {
        foreach (self::HEADER_ALIASES as $field => $aliases) {
            if (in_array($header, $aliases, true)) {
                return $field;
            }
        }

        return null;
    }

    protected function toString(mixed $value): string
    {
        return trim((string) $value);
    }

    protected function toNullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    protected function normalizeDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }
}

