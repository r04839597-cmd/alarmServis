<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Ubah 08..., +628..., 8..., atau 628... (dengan spasi/strip) menjadi 628...
     */
    public static function sanitize(?string $raw): string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}
