<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    TelescopeServiceProvider::class,
];
