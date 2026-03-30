<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleDriveUrl implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_HOSTS = [
        'drive.google.com',
        'docs.google.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail('El campo :attribute debe ser una URL valida de Google Drive.');

            return;
        }

        $url = trim($value);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $fail('El campo :attribute debe ser una URL valida de Google Drive.');

            return;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (! in_array($host, self::ALLOWED_HOSTS, true)) {
            $fail('Solo se permiten enlaces de drive.google.com o docs.google.com.');
        }
    }
}

