<?php

namespace App\Support;

final class CancellationReasonOptions
{
    public const NO_LONGER_NEEDED = 'no_longer_needed';
    public const BOOKED_BY_MISTAKE = 'booked_by_mistake';
    public const FOUND_ANOTHER_FIXER = 'found_another_fixer';
    public const OTHER = 'other';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::NO_LONGER_NEEDED => 'I no longer need the service',
            self::BOOKED_BY_MISTAKE => 'I booked by mistake',
            self::FOUND_ANOTHER_FIXER => 'I found another fixer',
            self::OTHER => 'Other',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::labels());
    }

    public static function labelFor(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        return self::labels()[$key] ?? null;
    }
}
