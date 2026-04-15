<?php

namespace App\Services;

class AttachmentRuleService
{
    private const ATTACHMENT_FIELDS_BY_COMBO = [
        'national_id' => ['upload_pnpki', 'upload_national_id'],
        'passport_only' => ['upload_pnpki', 'upload_passport'],
        'umid_only' => ['upload_pnpki', 'upload_umid'],
        'drivers_license_only' => ['upload_pnpki', 'upload_drivers_license'],
        'prc_only' => ['upload_pnpki', 'upload_prc_id'],
        'postal_id_only' => ['upload_pnpki', 'upload_postal_id'],
        'birth_cert_umid' => ['upload_pnpki', 'upload_birth_cert', 'upload_umid'],
        'passport_umid' => ['upload_pnpki', 'upload_passport', 'upload_umid'],
        'birth_cert_valid_ids' => ['upload_pnpki', 'upload_birth_cert', 'upload_id1', 'upload_id2'],
        'passport_valid_ids' => ['upload_pnpki', 'upload_passport', 'upload_id1', 'upload_id2'],
    ];

    private const ATTACHMENT_TYPES_BY_FIELD = [
        'upload_pnpki' => 'PNPKI',
        'upload_national_id' => 'NationalID',
        'upload_birth_cert' => 'BirthCert',
        'upload_passport' => 'Passport',
        'upload_umid' => 'UMID',
        'upload_drivers_license' => 'DriversLicense',
        'upload_prc_id' => 'PRCID',
        'upload_postal_id' => 'PostalID',
        'upload_id1' => 'ID1',
        'upload_id2' => 'ID2',
    ];

    public function activeFieldsForCombo(?string $combo): array
    {
        return self::ATTACHMENT_FIELDS_BY_COMBO[$combo] ?? [];
    }

    public function allFields(): array
    {
        return array_keys(self::ATTACHMENT_TYPES_BY_FIELD);
    }

    public function fileTypeForField(string $field): ?string
    {
        return self::ATTACHMENT_TYPES_BY_FIELD[$field] ?? null;
    }

    public function fieldFromFileType(string $fileType): ?string
    {
        foreach (self::ATTACHMENT_TYPES_BY_FIELD as $field => $type) {
            if ($fileType === $type) {
                return $field;
            }
        }

        return null;
    }

    public function detectComboFromPaths(array $paths): ?string
    {
        foreach (self::ATTACHMENT_FIELDS_BY_COMBO as $combo => $requiredFields) {
            $allFieldsPresent = collect($requiredFields)->every(fn (string $field): bool => ! empty($paths[$field] ?? null));

            if ($allFieldsPresent) {
                return $combo;
            }
        }

        return null;
    }

    public function humanLabelForField(string $field): string
    {
        return match ($field) {
            'upload_pnpki' => 'PNPKI Form',
            'upload_national_id' => 'National ID',
            'upload_birth_cert' => 'Birth Certificate',
            'upload_passport' => 'Passport',
            'upload_umid' => 'UMID',
            'upload_drivers_license' => "Driver's License",
            'upload_prc_id' => 'PRC ID',
            'upload_postal_id' => 'Postal ID',
            'upload_id1' => 'Valid ID #1',
            'upload_id2' => 'Valid ID #2',
            default => $field,
        };
    }
}
