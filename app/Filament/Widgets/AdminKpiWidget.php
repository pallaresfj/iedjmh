<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\AdminDashboardData;
use Filament\Widgets\Widget;

class AdminKpiWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.admin-kpi-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'kpis' => app(AdminDashboardData::class)->kpis(),
        ];
    }
}
