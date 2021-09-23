<?php
declare(strict_types=1);

use App\Core;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

return [
    Filesystem::class => function () {
        return new Filesystem(new LocalFilesystemAdapter(Core::path('storage')));
    }
];
