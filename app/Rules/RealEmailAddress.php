<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class RealEmailAddress implements ValidationRule
{
    public static function rules(?int $ignoreUserId = null, bool $required = true): array
    {
        $unique = Rule::unique('users', 'email');
        if ($ignoreUserId !== null) {
            $unique = $unique->ignore($ignoreUserId);
        }

        return [
            'bail',
            $required ? 'required' : 'nullable',
            'string',
            'lowercase',
            'max:100',
            'email:rfc,dns',
            new self(),
            $unique,
        ];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        $email = trim($value);
        if (! str_contains($email, '@')) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        [$localPart, $domain] = explode('@', $email, 2);
        $localPart = trim($localPart);
        $domain = mb_strtolower(trim($domain));

        if ($localPart === '' || $domain === '') {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        $blockedDomains = array_map('mb_strtolower', (array) config('anti_fraud.emails.blocked_domains', []));
        if (in_array($domain, $blockedDomains, true)) {
            $fail('Please use a real email address. Disposable or placeholder email domains are not allowed.');
            return;
        }

        $blockedLocals = array_map('mb_strtolower', (array) config('anti_fraud.emails.blocked_local_parts_exact', []));
        if (in_array(mb_strtolower($localPart), $blockedLocals, true)) {
            $fail('Please use a real email address.');
            return;
        }

        foreach ((array) config('anti_fraud.emails.blocked_local_part_patterns', []) as $pattern) {
            if (@preg_match((string) $pattern, $localPart) === 1) {
                $fail('Please use a real email address.');
                return;
            }
        }

        // Common trusted domains are allowed directly. Other custom domains are allowed
        // as long as Laravel\'s email:rfc,dns rule passes and they are not blocked above.
        $allowedDomains = array_map('mb_strtolower', (array) config('anti_fraud.emails.allowed_domains', []));
        if (in_array($domain, $allowedDomains, true)) {
            return;
        }
    }
}
