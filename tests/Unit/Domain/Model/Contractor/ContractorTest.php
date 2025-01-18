<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Contractor;

use App\Domain\Models\Contractor\Contractor;
use App\Domain\Models\Contractor\ContractorWasRegistered;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('domain')]
#[Group('model')]
#[Group('contractor')]
#[CoversClass(Contractor::class)]
class ContractorTest extends BasicTestCase
{
    use ContractorFactoryMethods;

    public function testWeCanRegisterAContractor(): void
    {
        $aContractorId = $this->aContractorId();

        $contractor = Contractor::register(
            contractorId: $aContractorId,
        );

        self::assertArrayContainsObjectOfType(ContractorWasRegistered::class, $contractor->releaseEvents());

        self::assertSame($aContractorId, $contractor->contractorId());
    }
}
