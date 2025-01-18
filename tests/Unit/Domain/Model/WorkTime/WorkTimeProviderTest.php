<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\WorkTime;

use App\Domain\Models\WorkTime\DownloadedTasksForWorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProviderWasSetup;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('domain')]
#[Group('model')]
#[Group('work-time')]
#[CoversClass(WorkTimeProvider::class)]
class WorkTimeProviderTest extends BasicTestCase
{
    use WorkTimeFactoryMethods;

    public function testWeCanSetupAWorkTimeProvider(): void
    {
        $aWorkTimeProviderId = $this->aWorkTimeProviderId();
        $aWorkTimeProviderType = $this->aWorkTimeProviderType();
        $aCredentials = $this->aCredentials();

        $provider = WorkTimeProvider::setup(
            workTimeProviderId: $aWorkTimeProviderId,
            workTimeProviderType: $aWorkTimeProviderType,
            credentials: $aCredentials,
        );

        self::assertArrayContainsObjectOfType(WorkTimeProviderWasSetup::class, $provider->releaseEvents());

        self::assertSame($aWorkTimeProviderId, $provider->workTimeProviderId());
        self::assertSame($aWorkTimeProviderType, $provider->workTimeProviderType());
        self::assertSame($aCredentials, $provider->credentials());
    }

    public function testWeCanDownloadFromAWorkTimeProcessor(): void
    {
        $aProvider = $this->aWorkTimeProvider();
        $aWorkTimeProcessor = $this->aWorkTimeProcessor();
        $aWorkTimeFilterCriteria = $this->aWorkTimeFilterCriteria();

        $aProvider->download(
            processor: $aWorkTimeProcessor,
            filterCriteria: $aWorkTimeFilterCriteria,
        );

        self::assertArrayContainsObjectOfType(DownloadedTasksForWorkTimeProvider::class, $aProvider->releaseEvents());
    }
}
