<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\AdminDashboardData;
use Filament\Widgets\Widget;

class ContractingStatusWidget extends Widget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.contracting-status-widget';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 4,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'status' => app(AdminDashboardData::class)->contractingStatus(),
        ];
    }
}
