<?php

use App\Support\PublicIcon;

test('normalizes legacy material icon values to canonical format', function () {
    expect(PublicIcon::normalize('agriculture'))->toBe('ms:agriculture');
});

test('keeps canonical material and font awesome values', function () {
    expect(PublicIcon::normalize('ms:menu_book'))->toBe('ms:menu_book')
        ->and(PublicIcon::normalize('fa:solid:house'))->toBe('fa:solid:house')
        ->and(PublicIcon::normalize('fa:brands:facebook-f'))->toBe('fa:brands:facebook-f');
});

test('falls back to safe icon when value is invalid', function () {
    expect(PublicIcon::normalize('fa:ultra:house'))->toBe('ms:help')
        ->and(PublicIcon::normalize('icono invalido'))->toBe('ms:help')
        ->and(PublicIcon::normalize(''))->toBe('ms:help');
});

test('validates supported icon formats', function () {
    expect(PublicIcon::isValidInput('ms:agriculture'))->toBeTrue()
        ->and(PublicIcon::isValidInput('fa:solid:house'))->toBeTrue()
        ->and(PublicIcon::isValidInput('fa:brands:facebook-f'))->toBeTrue()
        ->and(PublicIcon::isValidInput('agriculture'))->toBeTrue()
        ->and(PublicIcon::isValidInput('fa:ultra:house'))->toBeFalse()
        ->and(PublicIcon::isValidInput(''))->toBeFalse()
        ->and(PublicIcon::isValidInput('fa:solid:house!!'))->toBeFalse();
});
