<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\AdminDashboardData;
use Filament\Widgets\Widget;

class UpcomingEventsWidget extends Widget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.upcoming-events-widget';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 4,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $data = app(AdminDashboardData::class);

        return [
            'events' => $data->upcomingEvents(),
            'createUrl' => $data->eventsCreateUrl(),
            'indexUrl' => $data->eventsIndexUrl(),
        ];
    }
}
