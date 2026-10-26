<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Infrastructure\Symfony;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Infrastructure\Symfony\ToolsExtension;
use Symfony\Component\Config\Definition\Processor;

#[Small]
#[CoversClass(ToolsExtension::class)]
class ToolsExtensionOrderNumberTest extends TestCase
{
    public function testThatOrganizationOrderNumbersAreOptionalAndPreserved(): void
    {
        $organization = [
            'project' => 'cheesecake-agile',
            'name' => 'BilledTo GmbH',
            'street' => 'Somestreet 1',
            'location' => '55555 Some',
            'vatId' => 'DE900000009',
            'description' => 'Consulting',
            'pricePerHour' => 70.0,
        ];

        $config = (new Processor())->processConfiguration(new ToolsExtension(), [[
            'organizations' => [
                'without_order' => $organization,
                'with_order' => [...$organization, 'orderNumber' => 'PO-2025-0042'],
            ],
        ]]);

        self::assertNull($config['organizations']['without_order']['orderNumber']);
        self::assertSame('PO-2025-0042', $config['organizations']['with_order']['orderNumber']);
    }
}
