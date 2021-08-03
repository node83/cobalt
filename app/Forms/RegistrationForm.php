<?php
declare(strict_types=1);

namespace App\Forms;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/**
 * @property $username
 * @property $password
 * @property $email
 */
class RegistrationForm extends AbstractForm
{
    /**
     * @param ServerRequestInterface|null $request
     */
    public function fromRequest(?ServerRequestInterface $request = null): void
    {
        $this->username = $request ? $this->getParam($request, 'username') : '';
        $this->password = $request ? $this->getParam($request, 'password') : '';
        $this->email = $request ? $this->getParam($request, 'email') : '';
    }

    /**
     * @return Validatable
     */
    public function validationRules(): Validatable
    {
        return Validator::arrayVal()
            ->key('username', Validator::stringVal()->length(4, 25)->alnum('-_.')->uniqueFor('users:username'))
            ->key('password', Validator::stringVal())
            ->key('email', Validator::email()->uniqueFor('users:email'));
    }
}
