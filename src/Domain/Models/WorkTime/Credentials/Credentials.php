<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime\Credentials;

interface Credentials
{
    public function authType(): AuthorizationType;

    /**
     * A way to attach a single key to credentials.
     *
     * Used in combination with a value for example username:password. Where username is the key.
     *
     * @throws MethodNotSupportedOnCredentials when an authorization type does not require a key
     *
     * @see BasicAuth
     * @see ApiKey
     */
    public function key(): string;

    /**
     * A way to attach a single value to credentials.
     *
     * Can be used in combination with a key for example username:password. Where password is the value.
     * Can also be used standalone where we only need a value for a given credential.
     *
     * @throws MethodNotSupportedOnCredentials when an authorization type does not require a value
     *
     * @see ApiKey
     * @see BasicAuth
     * @see BearerToken
     */
    public function value(): string;
}
