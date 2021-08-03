<?php
declare(strict_types=1);

namespace App\Forms;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

abstract class AbstractForm
{
    public ?string $error = null;

    protected array $fields;

    abstract public function fromRequest(?ServerRequestInterface $request = null): void;
    abstract public function validationRules(): Validatable;

    /**
     * AbstractForm constructor.
     */
    public function __construct()
    {
        $this->fromRequest();
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $input = [];

        $this->error = null;
        foreach ($this->fields as $key => $value) {
            $this->fields[$key]->error = false;
            $input[$key] = $value->value;
        }

        try {
            $this->validationRules()->assert($input);
        }
        catch (NestedValidationException $e) {
            foreach ($e->getMessages() as $key => $message) {
                $this->fields[$key]->error = $message;
            }

            return false;
        }

        $this->makeClean();

        return true;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $key
     * @param string $default
     * @return string
     */
    protected function getParam(ServerRequestInterface $request, string $key, string $default = ''): string
    {
        $params = $request->getParsedBody();

        return array_key_exists($key, $params) && is_string($params[$key]) ? trim($params[$key]) : $default;
    }

    /**
     * @return void
     */
    protected function makeClean(): void
    {
        foreach ($this->fields as $key => $value) {
            $this->fields[$key]->clean = $value->value;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->fields[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->fields[$key] = (object)[
            'value' => $value,
            'error' => false,
            'clean' => null,
        ];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->fields);
    }
}
