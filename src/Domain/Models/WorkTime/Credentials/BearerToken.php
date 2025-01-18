<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime\Credentials;

use Webmozart\Assert\Assert;

class BearerToken implements Credentials
{
    public function __construct(string $token)
    {
        Assert::notEmpty($token);
    }

    public static function fromString(string $token): self
    {
        return new self($token);
    }

    public function authType(): AuthorizationType
    {
        return AuthorizationType::BEARER_TOKEN;
    }

    public function getToken(): string
    {
        return $this->getToken();
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
