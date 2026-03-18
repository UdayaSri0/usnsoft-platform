<?php

namespace App\Modules\IdentityAccess\Services;

use App\Models\User;

class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $secret;
    }

    public function provisioningUri(User $user, string $secret): string
    {
        $issuer = (string) config('security.mfa.issuer', config('app.name', 'USNsoft'));
        $label = rawurlencode($issuer.':'.$user->email);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            rawurlencode($secret),
            rawurlencode($issuer),
        );
    }

    public function verifyCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $normalizedCode = preg_replace('/\D+/', '', $code) ?? '';

        if (strlen($normalizedCode) !== 6) {
            return false;
        }

        $binarySecret = $this->base32Decode($secret);
        if ($binarySecret === '') {
            return false;
        }

        $timestamp ??= time();
        $counter = (int) floor($timestamp / 30);

        foreach (range(-$window, $window) as $offset) {
            $otp = $this->generateHotp($binarySecret, $counter + $offset);

            if (hash_equals($otp, $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    public function currentCode(string $secret, ?int $timestamp = null): string
    {
        $binarySecret = $this->base32Decode($secret);

        if ($binarySecret === '') {
            return '';
        }

        $timestamp ??= time();

        return $this->generateHotp($binarySecret, (int) floor($timestamp / 30));
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }

        return $codes;
    }

    private function generateHotp(string $secret, int $counter): string
    {
        $counterBytes = pack('N*', 0).pack('N*', $counter);
        $hash = hash_hmac('sha1', $counterBytes, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        );

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');
        if ($secret === '') {
            return '';
        }

        $alphabet = array_flip(str_split(self::ALPHABET));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($secret) as $character) {
            if (! isset($alphabet[$character])) {
                return '';
            }

            $buffer = ($buffer << 5) | $alphabet[$character];
            $bitsLeft += 5;

            while ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
