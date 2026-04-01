<?php

use App\Models\Setting;
use App\Support\PublicSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin login injects theme variables from settings palette', function () {
    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Admin Theme',
        'theme_primary' => '#123ABC',
        'theme_primary_dark' => '#102030',
        'theme_primary_light' => '#DDEEFF',
        'theme_accent' => '#FF9900',
        'theme_gray_900' => '#1F2937',
        'theme_gray_700' => '#374151',
        'theme_gray_600' => '#4B5563',
        'theme_gray_200' => '#E5E7EB',
        'theme_gray_100' => '#F3F4F6',
    ]);

    PublicSettings::clearCache();

    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('--color-ied-primary:#123ABC', false)
        ->assertSee('--color-ied-accent:#FF9900', false)
        ->assertSee('--color-ied-danger:#9B6614', false)
        ->assertSee('--color-ied-gray-100:#F3F4F6', false)
        ->assertSee('--color-ied-accent-rgb:255, 153, 0', false);
});

test('admin panel color aliases are resolved from settings palette', function () {
    Setting::query()->create([
        'singleton' => 1,
        'institution_name' => 'IED Panel Colors',
        'theme_primary' => '#123ABC',
        'theme_primary_dark' => '#102030',
        'theme_primary_light' => '#DDEEFF',
        'theme_accent' => '#FF9900',
        'theme_gray_900' => '#1F2937',
        'theme_gray_700' => '#374151',
        'theme_gray_600' => '#4B5563',
        'theme_gray_200' => '#E5E7EB',
        'theme_gray_100' => '#F3F4F6',
    ]);

    PublicSettings::clearCache();

    $colors = Filament::getPanel('admin')->getColors();

    expect($colors)
        ->toHaveKeys(['primary', 'success', 'info', 'warning', 'danger', 'gray'])
        ->and($colors['primary'])->toBe('#123ABC')
        ->and($colors['success'])->toBe('#254AC1')
        ->and($colors['info'])->toBe('#DFEFFF')
        ->and($colors['warning'])->toBe('#FF9900')
        ->and($colors['danger'])->toBe('#9B6614')
        ->and($colors['gray'])->toBeArray()
        ->and($colors['gray'][100])->toBe('#F3F4F6')
        ->and($colors['gray'][200])->toBe('#E5E7EB')
        ->and($colors['gray'][600])->toBe('#4B5563')
        ->and($colors['gray'][700])->toBe('#374151')
        ->and($colors['gray'][900])->toBe('#1F2937');
});
