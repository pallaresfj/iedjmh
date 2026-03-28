<?php

namespace App\Support\Pqrs;

use App\Models\PqrsRequest;
use Illuminate\Support\Str;

class TrackingCodeGenerator
{
    public function generate(): string
    {
        do {
            $code = 'PQRS-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        } while (PqrsRequest::query()->where('tracking_code', $code)->exists());

        return $code;
    }
}
