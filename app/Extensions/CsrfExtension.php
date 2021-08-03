<?php
declare(strict_types=1);

namespace App\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfExtension extends AbstractExtension
{
    protected string $fieldName;
    protected string $sessionVar;

    public function __construct(string $fieldName = 'csrf_token', string $sessionVar = 'csrf')
    {
        $this->fieldName = $fieldName;
        $this->sessionVar = $sessionVar;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_input', [$this, 'csrfInput'], ['is_safe' => ['html']]),
            new TwigFunction('csrf_token', [$this, 'csrfToken']),
        ];
    }

    /**
     * @return string
     */
    public function csrfInput(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        return '<input name="' . $this->fieldName . '", type="hidden" value="' . $this->getToken() . '">';
    }

    /**
     * @return string
     */
    public function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        return $this->getToken();
    }

    private function getToken(): string
    {
        $token = $_SESSION[$this->sessionVar] ?? null;
        if (is_null($token)) {
            $_SESSION[$this->sessionVar] = $token = uniqid('csrf.', true);
        }

        return $token;
    }
}
