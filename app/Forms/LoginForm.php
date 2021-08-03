<?php
declare(strict_types=1);

namespace App\Forms;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/**
 * Class LoginForm
 * @package App\Forms
 *
 * @property $username
 * @property $password
 */
class LoginForm extends AbstractForm
{
    /**
     * @param ServerRequestInterface|null $request
     */
    public function fromRequest(?ServerRequestInterface $request = null): void
    {
        $this->username = $request ? $this->getParam($request, 'username') : '';
        $this->password = $request ? $this->getParam($request, 'password') : '';
    }

    /**
     * @return Validatable
     */
    public function validationRules(): Validatable
    {
        return Validator::arrayVal()
            ->key('username', Validator::stringVal())
            ->key('password', Validator::stringVal());
    }
}
