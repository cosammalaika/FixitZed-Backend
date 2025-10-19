<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use App\Support\ProvinceDistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $map = config('provinces.map', []);
        if (empty($map)) {
            return;
        }

        DB::transaction(function () use ($map) {
            $provinceSlugs = [];

            foreach ($map as $provinceName => $districts) {
                if (! is_string($provinceName) || trim($provinceName) === '') {
                    continue;
                }

                $provinceSlug = Str::slug($provinceName);
                $provinceSlugs[] = $provinceSlug;

                $province = Province::updateOrCreate(
                    ['slug' => $provinceSlug],
                    ['name' => $provinceName]
                );

                $districtSlugs = [];

                foreach ((array) $districts as $districtName) {
                    if (! is_string($districtName)) {
                        continue;
                    }
                    $districtName = trim($districtName);
                    if ($districtName === '') {
                        continue;
                    }

                    $districtSlug = Str::slug($districtName);
                    $districtSlugs[] = $districtSlug;

                    District::updateOrCreate(
                        [
                            'province_id' => $province->id,
                            'slug' => $districtSlug,
                        ],
                        [
                            'name' => $districtName,
                        ]
                    );
                }

                if (! empty($districtSlugs)) {
                    District::where('province_id', $province->id)
                        ->whereNotIn('slug', $districtSlugs)
                        ->delete();
                }
            }

            if (! empty($provinceSlugs)) {
                Province::whereNotIn('slug', $provinceSlugs)->delete();
            }
        });

        ProvinceDistrict::refresh();
    }
}
