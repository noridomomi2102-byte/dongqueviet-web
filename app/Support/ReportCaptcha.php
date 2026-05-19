<?php

namespace App\Support;

use Illuminate\Support\Str;

class ReportCaptcha
{
    public const SESSION_KEY = 'report_captcha_code';

    public static function generate(): string
    {
        $code = strtoupper(Str::random(6));
        session([self::SESSION_KEY => $code]);

        return $code;
    }

    public static function current(): string
    {
        if (! session()->has(self::SESSION_KEY)) {
            return self::generate();
        }

        return (string) session(self::SESSION_KEY);
    }

    public static function validate(?string $input): bool
    {
        $expected = session(self::SESSION_KEY);

        if (! $expected || ! is_string($input)) {
            return false;
        }

        return strtoupper(trim($input)) === strtoupper($expected);
    }

    public static function refresh(): string
    {
        return self::generate();
    }
}
