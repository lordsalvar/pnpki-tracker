<?php

namespace App\Services;

use App\DataTransferObjects\PnpkiExtractedData;
use App\Enums\Sex;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Throwable;

class PnpkiFormExtractionService
{
    /**
     * DICT-PNPKI-FO-001 (Revised 2025) fillable PDF field names.
     *
     * @var array<string, string>
     */
    private const DICT_PNPKI_ACRO_MAP = [
        'text2' => 'lastname',
        'text3' => 'firstname',
        'text4' => 'middlename',
        'text5' => 'suffix',
        'text7' => 'birthDate',
        'text8' => 'tinNumber',
        'text9' => 'organization',
        'text10' => 'organizationalUnit',
        'text11' => 'houseNo',
        'text12' => 'barangay',
        'text13' => 'province',
        'text14' => 'phoneNumber',
        'text15' => 'street',
        'text16' => 'municipality',
        'text17' => 'zipCode',
        'text18' => 'email',
    ];

    private ?string $lastParserError = null;

    public function __construct(
        private readonly PsgcService $psgcService,
    ) {}

    public function extract(string $pdfPath): PnpkiExtractedData
    {
        if (! is_readable($pdfPath)) {
            throw new \InvalidArgumentException('The PNPKI form file could not be read.');
        }

        $rawFields = $this->extractAcroFormFields($pdfPath);

        $dictFormData = $this->extractFromDictPnpkiAcroForm($rawFields);
        if ($dictFormData !== null) {
            return $dictFormData;
        }

        $text = $this->extractText($pdfPath);

        if ($text === '' && $rawFields === []) {
            $parserError = $this->lastParserError;
            $detail = $parserError !== null
                ? ' (Parser error: '.$parserError.')'
                : '';
            throw new \RuntimeException(
                'No readable text was found in this PDF. Use a digitally filled PNPKI form or a PDF with selectable text—not a scanned image-only file.'.$detail
            );
        }

        $data = new PnpkiExtractedData(
            rawFields: $rawFields,
            firstname: $this->field($rawFields, $text, ['first name', 'firstname', 'given name', 'first_name']),
            middlename: $this->field($rawFields, $text, ['middle name', 'middlename', 'middle_name']),
            lastname: $this->field($rawFields, $text, ['last name', 'lastname', 'surname', 'last_name']),
            suffix: $this->normalizeSuffix($this->field($rawFields, $text, ['name extension', 'suffix', 'name_extension'])),
            sex: $this->normalizeSex($this->field($rawFields, $text, ['sex', 'gender'])),
            birthDate: $this->normalizeBirthDate($this->field($rawFields, $text, ['date of birth', 'birth date', 'birthdate', 'dob'])),
            tinNumber: $this->normalizeTin($this->field($rawFields, $text, ['tin', 'taxpayer identification'])),
            organization: $this->field($rawFields, $text, ['organization/agency/company', 'organization', 'agency', 'company']),
            organizationalUnit: $this->field($rawFields, $text, [
                'organizational unit/department/division',
                'organizational unit',
                'department',
                'division',
            ]),
            houseNo: $this->field($rawFields, $text, ['unit/room/house no', 'house no', 'unit no', 'house_no']),
            street: $this->field($rawFields, $text, ['street']),
            barangay: $this->field($rawFields, $text, ['barangay', 'brgy']),
            municipality: $this->field($rawFields, $text, ['municipality/city', 'municipality', 'city']),
            province: $this->field($rawFields, $text, ['province']),
            zipCode: $this->normalizeZipCode($this->field($rawFields, $text, ['zip code', 'zipcode', 'postal code'])),
            phoneNumber: $this->normalizePhone($this->field($rawFields, $text, ['mobile no', 'mobile number', 'phone', 'contact number'])),
            email: $this->normalizeEmail($this->field($rawFields, $text, [
                'official work email address',
                'official work email',
                'email address',
                'email',
            ])),
        );

        if ($data->filledFieldNames() === []) {
            throw new \RuntimeException(
                'Could not match PNPKI form fields in this PDF. Please review and enter your details manually.'
            );
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toFormState(PnpkiExtractedData $data, ?string $preserveOrganization = null): array
    {
        $state = [];

        if (filled($data->firstname)) {
            $state['firstname'] = $this->cleanName($data->firstname);
        }

        if (filled($data->middlename)) {
            $state['middlename'] = $this->cleanName($data->middlename);
        }

        if (filled($data->lastname)) {
            $state['lastname'] = $this->cleanName($data->lastname);
        }

        if (filled($data->suffix)) {
            $state['suffix'] = $data->suffix;
        }

        if (filled($data->sex)) {
            $state['sex'] = $data->sex;
        }

        if (filled($data->birthDate)) {
            $state['birth_date'] = $data->birthDate;
        }

        if (filled($data->tinNumber)) {
            $state['tin_number'] = $data->tinNumber;
        }

        if (filled($data->organizationalUnit)) {
            $state['organizational_unit'] = $this->cleanText($data->organizationalUnit);
        }

        if (filled($preserveOrganization)) {
            $state['organization'] = $preserveOrganization;
        } elseif (filled($data->organization)) {
            $state['organization'] = $this->cleanText($data->organization);
        }

        if (filled($data->houseNo)) {
            $state['house_no'] = $this->cleanText($data->houseNo);
        }

        if (filled($data->street)) {
            $state['street'] = $this->cleanText($data->street);
        }

        if (filled($data->email)) {
            $state['email'] = $data->email;
        }

        if (filled($data->phoneNumber)) {
            $state['phone_number'] = $data->phoneNumber;
        }

        if (filled($data->zipCode)) {
            $state['zip_code'] = $data->zipCode;
        }

        if (filled($data->province)) {
            $provinceCode = $this->psgcService->findProvinceCodeByName($data->province);
            if ($provinceCode !== null) {
                $state['province'] = $provinceCode;
            }
        }

        if (filled($data->municipality) && isset($state['province'])) {
            $municipalityCode = $this->psgcService->findMunicipalityCodeByName($state['province'], $data->municipality);
            if ($municipalityCode !== null) {
                $state['municipality'] = $municipalityCode;
            }
        }

        if (filled($data->barangay) && isset($state['municipality'])) {
            $barangayCode = $this->psgcService->findBarangayCodeByName($state['municipality'], $data->barangay);
            if ($barangayCode !== null) {
                $state['barangay'] = $barangayCode;
            }
        }

        return $state;
    }

    /**
     * @param  array<string, string>  $acroFields
     */
    private function extractFromDictPnpkiAcroForm(array $acroFields): ?PnpkiExtractedData
    {
        if (! isset($acroFields['text2'], $acroFields['text3'])) {
            return null;
        }

        $values = [];

        foreach (self::DICT_PNPKI_ACRO_MAP as $fieldKey => $property) {
            if (! isset($acroFields[$fieldKey])) {
                continue;
            }

            $values[$property] = trim($acroFields[$fieldKey]);
        }

        if ($values === []) {
            return null;
        }

        return new PnpkiExtractedData(
            rawFields: $acroFields,
            firstname: $values['firstname'] ?? null,
            middlename: $values['middlename'] ?? null,
            lastname: $values['lastname'] ?? null,
            suffix: $this->normalizeSuffix($values['suffix'] ?? null),
            sex: $this->extractSexFromDictCheckboxes($acroFields),
            birthDate: $this->normalizeBirthDate($values['birthDate'] ?? null),
            tinNumber: $this->normalizeTin($values['tinNumber'] ?? null),
            organization: $values['organization'] ?? null,
            organizationalUnit: $values['organizationalUnit'] ?? null,
            houseNo: $values['houseNo'] ?? null,
            street: $values['street'] ?? null,
            barangay: $values['barangay'] ?? null,
            municipality: $values['municipality'] ?? null,
            province: $values['province'] ?? null,
            zipCode: $this->normalizeZipCode($values['zipCode'] ?? null),
            phoneNumber: $this->normalizePhone($values['phoneNumber'] ?? null),
            email: $this->normalizeEmail($values['email'] ?? null),
        );
    }

    /**
     * @param  array<string, string>  $acroFields
     */
    private function extractSexFromDictCheckboxes(array $acroFields): ?string
    {
        $isChecked = fn (string $key): bool => strtolower($acroFields[$key] ?? '') === 'yes';

        if ($isChecked('checkbox22')) {
            return Sex::Male->value;
        }

        if ($isChecked('checkbox23')) {
            return Sex::Female->value;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function extractAcroFormFields(string $pdfPath): array
    {
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($pdfPath);
            $fields = [];

            foreach ($pdf->getObjects() as $object) {
                if (! method_exists($object, 'getHeader') || ! method_exists($object, 'getDetails')) {
                    continue;
                }

                $details = $object->getDetails();
                if (! isset($details['Subtype']) || $details['Subtype'] !== 'Widget') {
                    continue;
                }

                if (! isset($details['T'])) {
                    continue;
                }

                $name = $this->normalizeFieldKey((string) $details['T']);
                $value = trim((string) ($details['V'] ?? $details['DV'] ?? ''));

                if ($name !== '' && $value !== '') {
                    $fields[$name] = $value;
                }
            }

            return $fields;
        } catch (Throwable $e) {
            $this->lastParserError = $e->getMessage();
            Log::warning('PnpkiFormExtractionService: AcroForm parsing failed', [
                'path' => $pdfPath,
                'error' => $e->getMessage(),
                'php_version' => PHP_VERSION,
            ]);

            return [];
        }
    }

    private function extractText(string $pdfPath): string
    {
        try {
            $parser = new Parser;

            return trim($parser->parseFile($pdfPath)->getText());
        } catch (Throwable $e) {
            $this->lastParserError ??= $e->getMessage();
            Log::warning('PnpkiFormExtractionService: text extraction failed', [
                'path' => $pdfPath,
                'error' => $e->getMessage(),
                'php_version' => PHP_VERSION,
            ]);

            return '';
        }
    }

    /**
     * @param  array<string, string>  $acroFields
     * @param  list<string>  $labels
     */
    private function field(array $acroFields, string $text, array $labels): ?string
    {
        foreach ($labels as $label) {
            $fromText = $this->matchLabelInText($text, $label);
            if ($fromText !== null && ! $this->looksLikeFormLabel($fromText)) {
                return $fromText;
            }
        }

        foreach ($labels as $label) {
            $key = $this->normalizeFieldKey($label);

            foreach ($acroFields as $fieldName => $value) {
                if ($fieldName === $key && filled(trim($value))) {
                    return trim($value);
                }
            }
        }

        return null;
    }

    private function looksLikeFormLabel(string $value): bool
    {
        $normalized = Str::of($value)->lower()->squish()->toString();

        $labelFragments = [
            'last name',
            'first name',
            'middle name',
            'name extension',
            'date of birth',
            'organization',
            'contact details',
            'municipality',
            'barangay',
            'province',
            'zip code',
            'mobile no',
            'email address',
            'official work email',
        ];

        foreach ($labelFragments as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }

    private function matchLabelInText(string $text, string $label): ?string
    {
        $escaped = preg_quote($label, '/');
        $pattern = '/'.$escaped.'\s*(?:\([^)]*\))?\s*[:\-]?\s*([^\r\n]+)/iu';

        if (preg_match($pattern, $text, $matches)) {
            $value = trim($matches[1]);
            $value = preg_replace('/\s{2,}/', ' ', $value) ?? $value;

            return filled($value) ? $value : null;
        }

        $nextLinePattern = '/'.$escaped.'\s*(?:\([^)]*\))?\s*\r?\n\s*([^\r\n]+)/iu';
        if (preg_match($nextLinePattern, $text, $matches)) {
            $value = trim($matches[1]);

            return filled($value) ? $value : null;
        }

        return null;
    }

    private function normalizeFieldKey(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function normalizeSuffix(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $normalized = Str::of($value)->trim()->lower()->toString();

        if (in_array($normalized, ['n/a', 'na', 'none', '-'], true)) {
            return 'N/A';
        }

        $map = [
            'jr' => 'Jr.',
            'jr.' => 'Jr.',
            'sr' => 'Sr.',
            'sr.' => 'Sr.',
        ];

        return $map[$normalized] ?? Str::of($value)->trim()->toString();
    }

    private function normalizeSex(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $normalized = Str::of($value)->lower()->trim()->toString();

        if (str_contains($normalized, 'female') || $normalized === 'f') {
            return Sex::Female->value;
        }

        if (str_contains($normalized, 'male') || $normalized === 'm') {
            return Sex::Male->value;
        }

        return null;
    }

    private function normalizeBirthDate(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        foreach (['m/d/Y', 'm-d-Y', 'd/m/Y', 'Y-m-d', 'F d, Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (InvalidFormatException) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (InvalidFormatException) {
            return null;
        }
    }

    private function normalizeTin(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';

        return strlen($digits) === 9 ? $digits : null;
    }

    private function normalizeZipCode(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';

        return strlen($digits) === 4 ? $digits : null;
    }

    private function normalizePhone(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0'.$digits;
        }

        return preg_match('/^09\d{9}$/', $digits) ? $digits : null;
    }

    private function normalizeEmail(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $email = Str::of($value)->trim()->lower()->toString();

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function cleanName(string $value): string
    {
        return Str::of($value)->trim()->title()->toString();
    }

    private function cleanText(string $value): string
    {
        return Str::of($value)->trim()->squish()->toString();
    }
}
