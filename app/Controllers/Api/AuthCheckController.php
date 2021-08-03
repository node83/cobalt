<?php
declare(strict_types=1);

use App\Controllers\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthCheckController extends AbstractController
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

    }
}
