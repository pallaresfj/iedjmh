<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\AdminDashboardData;
use Filament\Widgets\Widget;

class RecentMatriculaWidget extends Widget
{
    protected static ?int $sort = 7;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.recent-matricula-widget';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 8,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'items' => app(AdminDashboardData::class)->recentMatriculas(),
        ];
    }
}
