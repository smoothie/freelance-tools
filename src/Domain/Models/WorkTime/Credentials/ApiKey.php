<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime\Credentials;

use Webmozart\Assert\Assert;

class ApiKey implements Credentials
{
    public function __construct(private string $key, private string $value)
    {
        Assert::notEmpty($key);
    }

    public static function fromString(string $key, string $value): self
    {
        return new self($key, $value);
    }

    public function authType(): AuthorizationType
    {
        return AuthorizationType::API_KEY;
    }

    public function getToken(): string
    {
        $this->unsupported('getToken');
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }

    private function unsupported(string $method): void
    {
        throw MethodNotSupportedOnCredentials::onMethodForType(
            \sprintf('%s::%s()', get_debug_type($this), $method),
            $this->authType(),
        );
    }
}
