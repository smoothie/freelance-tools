<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime\Credentials;

class MethodNotSupportedOnCredentials extends \LogicException
{
    public static function onMethodForType(string $method, AuthorizationType $authType): self
    {
        return new self(
            \sprintf(
                'Method "%s" is not supported for "%s" credentials. Try to use another method or a different authorization type.',
                $method,
                $authType->value,
            ),
        );
    }
}
