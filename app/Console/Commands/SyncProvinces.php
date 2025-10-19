<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Support\ProvinceDistrict;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SyncProvinces extends Command
{
    protected $signature = 'fixitzed:sync-provinces {--url=}';
    protected $description = 'Sync provinces and districts from the Zeydian dataset into the local database.';

    public function handle(): int
    {
        $url = $this->option('url')
            ?? config('services.zeydian.provinces_url')
            ?? 'https://data.zeydian.com/api/provinces';

        $this->info("Fetching provinces from {$url}");

        $normalized = collect();

        try {
            $response = Http::timeout(20)->acceptJson()->get($url);

            if ($response->successful()) {
                $normalized = $this->normalizePayload($response->json());
            } else {
                $this->warn("Provinces API returned HTTP {$response->status()}");
            }
        } catch (Throwable $e) {
            $this->warn("Failed to call provinces API: {$e->getMessage()}");
        }

        if ($normalized->isEmpty()) {
            $this->warn('Falling back to local config dataset.');
            $normalized = $this->mapFromConfig();
        }

        if ($normalized->isEmpty()) {
            $this->error('No province data available to sync.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($normalized) {
            $provinceIds = [];

            foreach ($normalized as $entry) {
                $province = Province::updateOrCreate(
                    ['slug' => $entry['slug']],
                    ['name' => $entry['name']]
                );
                $provinceIds[] = $province->id;

                $districtSlugs = [];
                foreach ($entry['districts'] as $districtName) {
                    $districtSlug = Str::slug($districtName);
                    District::updateOrCreate(
                        [
                            'province_id' => $province->id,
                            'slug' => $districtSlug,
                        ],
                        [
                            'name' => $districtName,
                        ]
                    );
                    $districtSlugs[] = $districtSlug;
                }

                District::where('province_id', $province->id)
                    ->whereNotIn('slug', $districtSlugs)
                    ->delete();
            }

            Province::whereNotIn('id', $provinceIds)->delete();
        });

        ProvinceDistrict::refresh();
        $this->info('Provinces and districts synced successfully.');

        return self::SUCCESS;
    }

    /**
     * Normalizes the remote payload into a collection of
     * ['name' => string, 'slug' => string, 'districts' => array<string>].
     */
    private function normalizePayload(mixed $payload): Collection
    {
        $items = collect();
        $candidates = $this->unwrapPayload($payload);

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $provinceName = $this->extractName($candidate);
            if ($provinceName === null) {
                continue;
            }

            $districts = $this->extractDistricts($candidate);
            if ($districts->isEmpty()) {
                continue;
            }

            $items->push([
                'name' => $provinceName,
                'slug' => Str::slug($provinceName),
                'districts' => $districts->unique()->values()->all(),
            ]);
        }

        return $items;
    }

    /**
     * @return Collection<int, array{name: string, slug: string, districts: array<int, string>}>
     */
    private function mapFromConfig(): Collection
    {
        $raw = config('provinces.map', []);
        if (! is_array($raw)) {
            return collect();
        }

        $items = collect();
        foreach ($raw as $provinceName => $districts) {
            if (! is_string($provinceName) || trim($provinceName) === '') {
                continue;
            }

            $list = collect((array) $districts)
                ->filter(static fn ($item) => is_string($item) && trim($item) !== '')
                ->map(static fn ($item) => trim($item))
                ->unique()
                ->values();

            if ($list->isEmpty()) {
                continue;
            }

            $items->push([
                'name' => trim($provinceName),
                'slug' => Str::slug($provinceName),
                'districts' => $list->all(),
            ]);
        }

        return $items;
    }

    /**
     * Attempt to unwrap different response shapes into a flat array.
     *
     * @return array<int, mixed>
     */
    private function unwrapPayload(mixed $payload): array
    {
        if (is_array($payload)) {
            if ($this->isList($payload)) {
                return $payload;
            }

            foreach (['data', 'provinces', 'results'] as $key) {
                if (isset($payload[$key]) && is_array($payload[$key])) {
                    $inner = $payload[$key];
                    return $this->isList($inner) ? $inner : array_values($inner);
                }
            }

            return array_values($payload);
        }

        return [];
    }

    private function extractName(array $payload): ?string
    {
        foreach (['name', 'province', 'province_name', 'title'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                $value = trim($payload[$key]);
                if ($value !== '') {
                    return $this->normalizeCase($value);
                }
            }
        }

        return null;
    }

    /**
     * @return Collection<int, string>
     */
    private function extractDistricts(array $payload): Collection
    {
        $candidates = collect();
        foreach (['districts', 'district_list', 'areas'] as $key) {
            if (isset($payload[$key])) {
                $candidates = collect($payload[$key]);
                break;
            }
        }

        if ($candidates->isEmpty()) {
            return collect();
        }

        return $candidates
            ->flatMap(function ($item) {
                if (is_string($item)) {
                    $value = trim($item);
                    return $value === '' ? [] : [$this->normalizeCase($value)];
                }

                if (is_array($item)) {
                    foreach (['name', 'district', 'title'] as $key) {
                        if (isset($item[$key]) && is_string($item[$key])) {
                            $value = trim($item[$key]);
                            if ($value !== '') {
                                return [$this->normalizeCase($value)];
                            }
                        }
                    }
                }

                return [];
            })
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->values();
    }

    private function normalizeCase(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        return collect(explode(' ', $value))
            ->filter(static fn ($part) => $part !== '')
            ->map(static fn ($part) => Str::ucfirst(Str::lower($part)))
            ->implode(' ');
    }

    private function isList(array $value): bool
    {
        return array_is_list($value);
    }
}
