<?php
declare(strict_types=1);

use App\Core;
use App\Extensions\CoreExtension;
use App\Extensions\CsrfExtension;
use Slim\Views\Twig;

return [

    Twig::class => static function () {
        $instance = Twig::create(Core::path('templates'), [
            'cache' => false,
            'debug' => true,
            'strict_variables' => true,
        ]);

        $instance->getEnvironment()->addExtension(new CoreExtension());
        $instance->getEnvironment()->addExtension(new CsrfExtension());

        return $instance;
    },

];
