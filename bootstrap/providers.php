<?php

use App\Providers\AppServiceProvider;
use App\Providers\BroadcastServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;

return [
    AppServiceProvider::class,
    BroadcastServiceProvider::class,
    SanctumServiceProvider::class,
];
