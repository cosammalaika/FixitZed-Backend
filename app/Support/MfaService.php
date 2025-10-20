<?php

namespace App\Support;

use Illuminate\Support\Str;

class MfaService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes($length);
        return $this->base32Encode($bytes);
    }

    public function makeQrUrl(string $issuer, string $account, string $secret): string
    {
        $label = rawurlencode($issuer . ':' . $account);
        $issuerEncoded = rawurlencode($issuer);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&period=30',
            $label,
            $secret,
            $issuerEncoded
        );
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $secret = strtoupper($secret);
        $timeSlice = floor(time() / 30);
        $code = preg_replace('/\s+/', '', $code);

        for ($i = -$window; $i <= $window; $i++) {
            if ($this->calculateCode($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    public function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        if ($secretKey === false) {
            return '';
        }

        $time = pack('N*', 0, $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord($hash[19]) & 0xf;
        $value = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        );

        $modulo = $value % 1000000;

        return str_pad((string) $modulo, 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $alphabet = self::BASE32_ALPHABET;
        $binaryString = '';

        foreach (str_split($data) as $char) {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binaryString, 5);
        $base32 = '';

        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $base32 .= $alphabet[bindec($chunk)];
        }

        return $base32;
    }

    private function base32Decode(string $data)
    {
        $alphabet = self::BASE32_ALPHABET;
        $data = strtoupper($data);
        $binaryString = '';

        foreach (str_split($data) as $char) {
            $position = strpos($alphabet, $char);
            if ($position === false) {
                return false;
            }
            $binaryString .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binaryString, 8);
        $result = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 8) {
                continue;
            }
            $result .= chr(bindec($chunk));
        }

        return $result;
    }

    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(10));
        }
        return $codes;
    }
}
