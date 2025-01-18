<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime\Credentials;

class NoCredentials implements Credentials
{
    public function authType(): AuthorizationType
    {
        return AuthorizationType::NONE;
    }

    public function getToken(): string
    {
        $this->unsupported('getToken');
    }

    public function key(): string
    {
        $this->unsupported('getUsername');
    }

    public function value(): string
    {
        $this->unsupported('getPassword');
    }

    private function unsupported(string $method): void
    {
        throw MethodNotSupportedOnCredentials::onMethodForType(
            \sprintf('%s::%s()', get_debug_type($this), $method),
            $this->authType(),
        );
    }
}
