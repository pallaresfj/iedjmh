<?php

namespace App\Support\Contracts;

use App\Models\Contract;
use App\Models\ContractDocument;
use Illuminate\Support\Str;

class ContractPublicationValidator
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    public static function validate(array $data): array
    {
        if (($data['status'] ?? null) !== 'published') {
            return [];
        }

        $errors = [];
        $processStatus = self::normalizeStringValue($data['process_status'] ?? null);
        $documents = self::normalizeDocuments($data['documents'] ?? []);
        $participants = self::normalizeParticipants($data['participants'] ?? []);
        $presentDocumentTypes = [];
        $officialCounts = [];

        foreach ($documents as $index => $document) {
            $documentType = self::normalizeStringValue($document['document_type'] ?? null);
            $stage = self::normalizeStringValue($document['stage'] ?? null);
            $externalUrl = self::normalizeStringValue($document['external_url'] ?? null);
            $title = self::normalizeStringValue($document['title'] ?? null);

            if ($documentType === '') {
                continue;
            }

            $presentDocumentTypes[] = $documentType;

            if (ContractDocument::isOfficialType($documentType)) {
                $officialCounts[$documentType] = ($officialCounts[$documentType] ?? 0) + 1;
                $expectedStage = ContractDocument::expectedStageFor($documentType);

                if ($expectedStage !== null && $stage !== $expectedStage) {
                    $errors["documents.{$index}.stage"] = 'La etapa no corresponde al tipo de documento seleccionado.';
                }
            }

            if ($documentType === 'otro' && $title === '') {
                $errors["documents.{$index}.title"] = 'El titulo es obligatorio para documentos de tipo "Otro".';
            }

            if ($externalUrl === '') {
                $errors["documents.{$index}.external_url"] = 'Cada documento debe tener URL externa.';
            }

            if ($externalUrl !== '' && ! self::isValidReferenceUrl($externalUrl)) {
                $errors["documents.{$index}.external_url"] = 'La URL externa debe ser http(s) o una ruta interna iniciando con "/".';
            }
        }

        foreach ($officialCounts as $documentType => $count) {
            if ($count <= 1) {
                continue;
            }

            $errors['documents'] = 'Solo se permite un documento por cada tipo oficial.';
            break;
        }

        $requiredTypes = Contract::requiredDocumentTypesForProcessStatus($processStatus);
        $missingTypes = array_values(array_diff($requiredTypes, array_unique($presentDocumentTypes)));

        if ($missingTypes !== []) {
            $missingLabels = collect($missingTypes)
                ->map(fn (string $type): string => ContractDocument::labelForType($type))
                ->implode(', ');

            if (! array_key_exists('documents', $errors)) {
                $errors['documents'] = "Faltan documentos requeridos para publicar: {$missingLabels}.";
            }
        }

        $seenParticipantContractorIds = [];
        $seenParticipantNits = [];

        foreach ($participants as $index => $participant) {
            $contractorId = self::normalizeContractorId($participant['contractor_id'] ?? null);
            $nit = self::normalizeNit(self::normalizeStringValue($participant['nit'] ?? null));

            if ($contractorId !== null) {
                if (isset($seenParticipantContractorIds[$contractorId])) {
                    $errors["participants.{$index}.contractor_id"] = 'No puedes registrar dos veces el mismo oferente.';
                } else {
                    $seenParticipantContractorIds[$contractorId] = true;
                }
            }

            if ($nit !== '') {
                if (isset($seenParticipantNits[$nit])) {
                    $errors["participants.{$index}.nit"] = 'No puedes registrar dos veces el mismo oferente (NIT duplicado).';
                } else {
                    $seenParticipantNits[$nit] = true;
                }
            }
        }

        if ($processStatus === 'adjudicado') {
            if ($participants === []) {
                $errors['participants'] = 'Debes registrar al menos un oferente/contratista para publicar un proceso adjudicado.';
            } else {
                $awardedIndexes = [];

                foreach ($participants as $index => $participant) {
                    if (self::isTrue($participant['is_awarded'] ?? null)) {
                        $awardedIndexes[] = $index;
                    }
                }

                if (count($awardedIndexes) !== 1) {
                    $errors['participants_awarded'] = 'Debes marcar exactamente un participante como adjudicado.';
                } else {
                    $awardedParticipant = $participants[$awardedIndexes[0]] ?? null;
                    $awardedName = self::normalizeStringValue($awardedParticipant['name'] ?? null);
                    $awardedNit = self::normalizeStringValue($awardedParticipant['nit'] ?? null);
                    $awardedSocialObject = self::normalizeStringValue($awardedParticipant['social_object'] ?? null);

                    if ($awardedName === '') {
                        $errors["participants.{$awardedIndexes[0]}.name"] = 'El participante adjudicado debe tener nombre.';
                    }

                    if ($awardedNit === '') {
                        $errors["participants.{$awardedIndexes[0]}.nit"] = 'El participante adjudicado debe tener NIT.';
                    }

                    if ($awardedSocialObject === '') {
                        $errors["participants.{$awardedIndexes[0]}.social_object"] = 'El participante adjudicado debe tener objeto social.';
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeDocuments(mixed $documents): array
    {
        if (! is_array($documents)) {
            return [];
        }

        return collect($documents)
            ->filter(fn (mixed $document): bool => is_array($document))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeParticipants(mixed $participants): array
    {
        if (! is_array($participants)) {
            return [];
        }

        return collect($participants)
            ->filter(fn (mixed $participant): bool => is_array($participant))
            ->values()
            ->all();
    }

    private static function normalizeStringValue(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $normalized = self::normalizeStringValue($item);

                if ($normalized !== '') {
                    return $normalized;
                }
            }
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value);
        }

        return '';
    }

    private static function isTrue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes'], true);
        }

        return false;
    }

    private static function normalizeContractorId(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private static function normalizeNit(string $value): string
    {
        $normalized = Str::upper(trim($value));

        return (string) preg_replace('/[^A-Z0-9]/', '', $normalized);
    }

    private static function isValidReferenceUrl(string $value): bool
    {
        if (Str::startsWith($value, '/')) {
            return true;
        }

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
