<?php

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;

test('admin panel polls database notifications every 120 seconds', function () {
    $provider = new AdminPanelProvider(app());
    $panel = $provider->panel(Panel::make());

    expect($panel->hasDatabaseNotifications())->toBeTrue()
        ->and($panel->getDatabaseNotificationsPollingInterval())->toBe('120s');
});

