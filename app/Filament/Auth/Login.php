<?php

namespace App\Filament\Auth;

use App\Support\PublicSettings;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected string $view = 'filament.auth.login';

    protected Width|string|null $maxContentWidth = Width::Full;

    /**
     * @var array<mixed>
     */
    protected array $extraBodyAttributes = [
        'class' => 'agro-admin-login-screen',
    ];

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function getInstitutionName(): string
    {
        return (string) PublicSettings::get(
            'institution_name',
            config('institution.display_name', config('institution.name', 'IED JOSÉ MARÍA HERRERA')),
        );
    }

    public function getInstitutionLogoUrl(): ?string
    {
        return PublicSettings::mediaUrl(PublicSettings::get('logo_path'));
    }

    public function getHeroBackgroundUrl(): string
    {
        $settingsImagePath = PublicSettings::get('home_hero_image_path');
        $settingsImageUrl = is_string($settingsImagePath)
            ? PublicSettings::mediaUrl($settingsImagePath)
            : null;

        return filled($settingsImageUrl)
            ? $settingsImageUrl
            : PublicSettings::homeHeroFallbackImageUrl();
    }
}
