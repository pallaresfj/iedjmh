<?php

namespace App\Observers;

use App\Models\PqrsMessage;
use App\Notifications\PqrsResponseNotification;

class PqrsMessageObserver
{
    public function created(PqrsMessage $message): void
    {
        if ($message->is_internal) {
            return;
        }

        if ($message->user_id === null) {
            return;
        }

        $pqrsRequest = $message->request;

        if (! $pqrsRequest || ! filled($pqrsRequest->applicant_email)) {
            return;
        }

        $pqrsRequest->notify(new PqrsResponseNotification($pqrsRequest, $message));
    }
}
