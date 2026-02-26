<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RealHumanName implements ValidationRule
{
    public static function rules(bool $required = true): array
    {
        return array_values(array_filter([
            'bail',
            $required ? 'required' : 'nullable',
            'string',
            'min:2',
            'max:60',
            new self(),
        ]));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('The :attribute must be a valid full name.');
            return;
        }

        $normalized = $this->normalizeWhitespace($value);
        $length = mb_strlen($normalized);
        $min = (int) config('anti_fraud.names.min_length', 2);
        $max = (int) config('anti_fraud.names.max_length', 60);

        if ($length < $min || $length > $max) {
            $fail("The :attribute must be between {$min} and {$max} characters.");
            return;
        }

        $lower = mb_strtolower($normalized);
        foreach ((array) config('anti_fraud.names.blocked_substrings', []) as $snippet) {
            $needle = mb_strtolower(trim((string) $snippet));
            if ($needle !== '' && str_contains($lower, $needle)) {
                $fail('The :attribute contains invalid text.');
                return;
            }
        }

        if (preg_match('/\d/u', $normalized)) {
            $fail('The :attribute may not contain numbers.');
            return;
        }

        if (! preg_match("/^[\\pL\\s'\\-]+$/u", $normalized)) {
            $fail('The :attribute may only contain letters, spaces, apostrophes, and hyphens.');
            return;
        }

        $words = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) < 2) {
            $fail('The :attribute must include at least a first name and last name.');
            return;
        }

        foreach ($words as $word) {
            if (! preg_match("/^[\\pL]+(?:['\\-][\\pL]+)*$/u", $word)) {
                $fail('The :attribute format is invalid.');
                return;
            }
        }

        $normalizedWords = array_map(function (string $word): string {
            return preg_replace('/[^\\pL]/u', '', mb_strtolower($word)) ?? '';
        }, $words);

        for ($i = 1; $i < count($normalizedWords); $i++) {
            if ($normalizedWords[$i] !== '' && $normalizedWords[$i] === $normalizedWords[$i - 1]) {
                $fail('The :attribute appears to repeat the same word.');
                return;
            }
        }

        $blocked = array_map([$this, 'normalizeComparableValue'], (array) config('anti_fraud.names.blocked_phrases', []));
        $comparableFull = $this->normalizeComparableValue($normalized);

        if (in_array($comparableFull, $blocked, true)) {
            $fail('Please enter your real first and last name.');
            return;
        }

        foreach ($words as $word) {
            $comparableWord = $this->normalizeComparableValue($word);
            if ($comparableWord !== '' && in_array($comparableWord, $blocked, true)) {
                $fail('Please enter your real first and last name.');
                return;
            }
        }
    }

    private function normalizeWhitespace(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', trim($value)));
    }

    private function normalizeComparableValue(string $value): string
    {
        $value = $this->normalizeWhitespace($value);
        $value = mb_strtolower($value);
        $value = preg_replace('/[^\\pL\s]/u', ' ', $value) ?? $value;
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
