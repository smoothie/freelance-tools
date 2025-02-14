<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Common\Duration;
use App\Domain\Models\Timing\TimingId;
use App\Domain\Models\WorkTime\Credentials\ApiKey;
use App\Domain\Models\WorkTime\Credentials\AuthorizationType;
use App\Domain\Models\WorkTime\Credentials\BasicAuth;
use App\Domain\Models\WorkTime\Credentials\BearerToken;
use App\Domain\Models\WorkTime\Credentials\Credentials;
use App\Domain\Models\WorkTime\Credentials\NoCredentials;
use App\Domain\Models\WorkTime\WorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProviderId;
use App\Domain\Models\WorkTime\WorkTimeProviderType;

class SetupWorkTimeProvider
{
    private function __construct(
        private string $workTimeProviderId,
        private string $workTimeProviderType,
        private string $credentialType,
        private string $credentialKey,
        private string $credentialValue,
    ) {
    }

    public function workTimeProviderId() : WorkTimeProviderId
    {
        return WorkTimeProviderId::fromString($this->workTimeProviderId);
    }

    public function workTimeProviderType() : WorkTimeProviderType
    {
        return WorkTimeProviderType::from($this->workTimeProviderType);
    }

    public function credentials() : Credentials
    {
        return match (AuthorizationType::tryFrom($this->credentialType)) {
            AuthorizationType::API_KEY => new ApiKey(key: $this->credentialKey, value: $this->credentialValue),
            AuthorizationType::BASIC_AUTH => new BasicAuth(key: $this->credentialKey, value: $this->credentialValue),
            AuthorizationType::BEARER_TOKEN => new BearerToken(token: $this->credentialValue),
            default => new NoCredentials()
        };
    }
}
