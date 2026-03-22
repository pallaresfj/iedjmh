<?php

namespace App\Filament\Widgets;

use App\Support\Dashboard\AdminDashboardData;
use Filament\Widgets\Widget;

class PendingNewsModerationWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.pending-news-moderation-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        if ((bool) ($user->is_admin ?? false) === true) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('Update:Post')) {
            return true;
        }

        return method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['editor', 'administrador']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'moderation' => app(AdminDashboardData::class)->pendingNewsModeration(),
        ];
    }
}
