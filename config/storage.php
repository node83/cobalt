<?php
declare(strict_types=1);

use App\Core;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;

return [

    'local' => [
        'driver' => LocalFilesystemAdapter::class,
        'params' => [
            'location' => Core::path('storage/app'),
            'visibility' => PortableVisibilityConverter::fromArray([], Visibility::PUBLIC),
        ],
    ],

    'public' => [
        'driver' => LocalFilesystemAdapter::class,
        'params' => [
            'location' => Core::path('storage/app/public'),
            'visibility' => PortableVisibilityConverter::fromArray([], Visibility::PUBLIC),
        ],
    ],
];
