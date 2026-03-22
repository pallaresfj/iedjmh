<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\AdminDashboardData;
use Filament\Widgets\Widget;

class RecentNewsWidget extends Widget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.recent-news-widget';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 8,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $data = app(AdminDashboardData::class);

        return [
            'items' => $data->recentNews(),
            'indexUrl' => $data->postsIndexUrl(),
            'createUrl' => $data->postsCreateUrl(),
        ];
    }
}
