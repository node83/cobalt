<?php
declare(strict_types=1);

namespace App\Controllers;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends Controller
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render($request, 'index.twig');
    }
}
