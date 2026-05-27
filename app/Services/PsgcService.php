<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PsgcService
{
    protected function load(string $file): Collection
    {
        $path = storage_path("app/psgc/{$file}.json");

        return collect(json_decode(file_get_contents($path), true));
    }

    public function provinces(): array
    {
        return $this->load('provinces')
            ->sortBy('province_name')
            ->pluck('province_name', 'province_code')
            ->toArray();
    }

    public function municipalities(string $provinceCode): array
    {
        return $this->load('municipalities')
            ->where('province_code', $provinceCode)
            ->sortBy('city_name')
            ->pluck('city_name', 'city_code')
            ->toArray();
    }

    public function barangays(string $municipalityCode): array
    {
        return $this->load('barangays')
            ->where('city_code', $municipalityCode)
            ->sortBy('brgy_name')
            ->pluck('brgy_name', 'brgy_code')
            ->toArray();
    }

    public function findProvinceCodeByName(string $name): ?string
    {
        return $this->findCodeByName($this->load('provinces'), 'province_name', 'province_code', $name);
    }

    public function findMunicipalityCodeByName(string $provinceCode, string $name): ?string
    {
        return $this->findCodeByName(
            $this->load('municipalities')->where('province_code', $provinceCode),
            'city_name',
            'city_code',
            $name,
        );
    }

    public function findBarangayCodeByName(string $municipalityCode, string $name): ?string
    {
        return $this->findCodeByName(
            $this->load('barangays')->where('city_code', $municipalityCode),
            'brgy_name',
            'brgy_code',
            $name,
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $collection
     */
    private function findCodeByName(Collection $collection, string $nameKey, string $codeKey, string $name): ?string
    {
        $needle = $this->normalizePlaceName($name);

        $exact = $collection->first(
            fn (array $row) => $this->normalizePlaceName((string) $row[$nameKey]) === $needle
        );

        if ($exact !== null) {
            return (string) $exact[$codeKey];
        }

        $bestMatch = null;
        $bestScore = 0.0;

        foreach ($collection as $row) {
            $candidate = $this->normalizePlaceName((string) $row[$nameKey]);
            similar_text($needle, $candidate, $percent);

            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestMatch = $row;
            }
        }

        return $bestScore >= 85 && $bestMatch !== null
            ? (string) $bestMatch[$codeKey]
            : null;
    }

    private function normalizePlaceName(string $name): string
    {
        return str($name)
            ->lower()
            ->replace(['city of ', 'municipality of '], '')
            ->replace([' city', ' municipality'], '')
            ->squish()
            ->toString();
    }
}
