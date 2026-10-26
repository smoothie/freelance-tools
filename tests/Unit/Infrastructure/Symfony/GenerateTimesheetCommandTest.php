<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Infrastructure\Symfony;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Application\ApplicationInterface;
use Smoothie\FreelanceTools\Application\GenerateTimesheet;
use Smoothie\FreelanceTools\Infrastructure\Symfony\Command\GenerateTimesheetCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[Small]
#[CoversClass(GenerateTimesheetCommand::class)]
#[CoversClass(GenerateTimesheet::class)]
#[UsesClass(\Smoothie\FreelanceTools\Domain\Model\ProjectId::class)]
final class GenerateTimesheetCommandTest extends TestCase
{
    #[DataProvider('templates')]
    public function testTemplateOptionIsPassedToTheApplication(?string $template): void
    {
        $application = $this->createMock(ApplicationInterface::class);
        $application->expects(self::once())->method('generateTimesheet')->with(self::callback(static function (GenerateTimesheet $command) use ($template): bool {
            return $command->templateId() === $template && $command->project()->asString() === 'CIL';
        }));
        $tester = new CommandTester(new GenerateTimesheetCommand($application, ['name' => 'Example Seller', 'street' => 'Example 1', 'location' => '10115 Berlin']));
        $input = ['project' => 'CIL', '--approvedBy' => 'Example Approver', '--approvedByCompany' => 'Example Buyer', '--billedTo' => 'Example Buyer', '--performancePeriod' => 'LAST_MONTH'];
        if ($template !== null) {
            $input['--template'] = $template;
        }

        self::assertSame(Command::SUCCESS, $tester->execute($input));
        self::assertStringContainsString('Timesheet generated.', $tester->getDisplay());
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function templates(): iterable
    {
        yield 'configured default' => [null];
        yield 'marc' => ['marc'];
        yield 'classic' => ['classic'];
    }

    public function testInvalidTemplateIsRejectedBeforeApplicationCalls(): void
    {
        $application = $this->createMock(ApplicationInterface::class);
        $application->expects(self::never())->method('generateTimesheet');
        $tester = new CommandTester(new GenerateTimesheetCommand($application, ['name' => 'Example Seller', 'street' => 'Example 1', 'location' => '10115 Berlin']));

        self::assertSame(Command::INVALID, $tester->execute(['project' => 'CIL', '--template' => '../other']));
        self::assertStringContainsString('Expected classic or marc', $tester->getDisplay());
    }

    public function testTypedRequestRejectsInvalidTemplate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GenerateTimesheet('example-id', 'CIL', ['name' => 'Approver', 'company' => 'Buyer'], ['name' => 'Seller', 'street' => 'Example 1', 'location' => 'Berlin'], templateId: 'other');
    }
}
