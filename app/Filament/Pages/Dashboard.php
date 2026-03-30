<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminKpiWidget;
use App\Filament\Widgets\ContractingStatusWidget;
use App\Filament\Widgets\PendingNewsModerationWidget;
use App\Filament\Widgets\RecentMatriculaWidget;
use App\Filament\Widgets\RecentNewsWidget;
use App\Filament\Widgets\RecentPqrsWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Inicio';

    protected static ?string $navigationLabel = 'Inicio';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return 12;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            AdminKpiWidget::class,
            PendingNewsModerationWidget::class,
            RecentNewsWidget::class,
            ContractingStatusWidget::class,
            RecentPqrsWidget::class,
            UpcomingEventsWidget::class,
            RecentMatriculaWidget::class,
        ];
    }
}
