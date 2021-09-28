<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Forms\RegistrationForm;
use App\Repositories\UserRepository;
use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterController extends Controller
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
        $form = new RegistrationForm();
        if ($request->getMethod() === 'POST') {
            $form->fromRequest($request);
            if ($form->isValid()) {
                $user = $this->users->addUser($form->username->value, $form->password->value, $form->email->value);
                if ($user) {
                    $_SESSION = ['user' => $user];
                    session_regenerate_id(true);
                    return $this->redirect($request, 'home');
                }

                $form->error = 'Unable to register account';
            }
        }

        return $this->render($request, 'register.twig', ['form' => $form]);
    }
}
