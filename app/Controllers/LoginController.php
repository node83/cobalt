<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Forms\LoginForm;
use App\Repositories\UserRepository;
use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController extends Controller
{
    /** @Inject */
    protected UserRepository $users;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $form = new LoginForm();
        if ($request->getMethod() === 'POST') {
            $form->fromRequest($request);
            if ($form->isValid()) {
                $user = $this->users->getByUsername($form->username->value);
                if ($user && password_verify($form->password->value, $user->password)) {
                    $_SESSION = ['user' => $user];
                    session_regenerate_id(true);
                    return $this->redirect($request, 'home');
                }
                $form->error = 'Invalid Credentials';
            }
        }

        return $this->render($request, 'login.twig', ['form' => $form]);
    }
}
