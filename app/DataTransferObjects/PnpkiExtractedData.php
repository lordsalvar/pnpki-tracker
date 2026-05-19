<?php

namespace App\DataTransferObjects;

readonly class PnpkiExtractedData
{
    /**
     * @param  array<string, string>  $rawFields
     */
    public function __construct(
        public array $rawFields = [],
        public ?string $firstname = null,
        public ?string $middlename = null,
        public ?string $lastname = null,
        public ?string $suffix = null,
        public ?string $sex = null,
        public ?string $birthDate = null,
        public ?string $tinNumber = null,
        public ?string $organization = null,
        public ?string $organizationalUnit = null,
        public ?string $houseNo = null,
        public ?string $street = null,
        public ?string $barangay = null,
        public ?string $municipality = null,
        public ?string $province = null,
        public ?string $zipCode = null,
        public ?string $phoneNumber = null,
        public ?string $email = null,
    ) {}

    /**
     * @return list<string>
     */
    public function filledFieldNames(): array
    {
        $fields = [
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'suffix' => $this->suffix,
            'sex' => $this->sex,
            'birth_date' => $this->birthDate,
            'tin_number' => $this->tinNumber,
            'organizational_unit' => $this->organizationalUnit,
            'house_no' => $this->houseNo,
            'street' => $this->street,
            'barangay' => $this->barangay,
            'municipality' => $this->municipality,
            'province' => $this->province,
            'zip_code' => $this->zipCode,
            'phone_number' => $this->phoneNumber,
            'email' => $this->email,
        ];

        return array_keys(array_filter($fields, fn (?string $value) => filled($value)));
    }
}
