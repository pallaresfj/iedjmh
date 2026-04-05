<?php

namespace App\Observers;

use App\Models\PqrsMessage;

class PqrsMessageObserver
{
    public function created(PqrsMessage $message): void
    {
        // Notification dispatch now lives in the explicit admin action
        // to avoid breaking the response flow if mail transport fails.
    }
}
