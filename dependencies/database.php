<?php
declare(strict_types=1);

use App\Classes\Database;
use App\Core;
use App\Providers\DatabaseAuthorizationProvider;
use Psr\Container\ContainerInterface;

return [

    Database::class => static function () {
        return new Database(Core::config('db.dsn'), Core::config('db.user'), Core::config('db.password'));
    },

    DatabaseAuthorizationProvider::class => function (ContainerInterface $ci) {
        return new DatabaseAuthorizationProvider($ci->get(Database::class));
    },

];
