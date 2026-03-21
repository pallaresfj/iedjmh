<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $setting = Setting::singleton();

        $this->redirect($this->getResource()::getUrl('edit', ['record' => $setting]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
