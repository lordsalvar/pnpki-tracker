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
}
