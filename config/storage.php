<?php
declare(strict_types=1);

use App\Core;
use League\Flysystem\Local\LocalFilesystemAdapter;

return [

    'local' => [
        'driver' => LocalFilesystemAdapter::class,
        'params' => [
            'location' => Core::path('storage/app'),
        ],
    ],

    'public' => [
        'driver' => LocalFilesystemAdapter::class,
        'params' => [
            'location' => Core::path('storage/app/public'),
        ],
    ],

];
