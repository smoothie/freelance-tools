<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Command;

use App\Application\ApplicationInterface;
use App\Application\GenerateTimesheet;
use App\Domain\Model\Common\DateTime;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'tools:generate-timesheet',
    description: 'Generate a timesheet',
)]
class GenerateTimesheetCommand extends Command
{
    private SymfonyStyle $io;

    /**
     * @param array{name: string, location: string, street: string} $providedBy
     */
    public function __construct(
        private ApplicationInterface $application,
        #[Autowire(param: 'tools.default_providedBy')]
        private array $providedBy,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $today = (new \DateTimeImmutable('now'))->format(DateTime::DATE_FORMAT);

        $this
            ->setDescription('Generate a timesheet')
            ->setHelp('This command allows you to generate a timesheet')
            ->addArgument(
                name: 'project',
                mode: InputArgument::REQUIRED,
                description: 'The project with the tracked tasks for the timesheet.',
            )
            ->addOption(
                name: 'approvedBy',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The person name who has or is going to approve the sheet.',
            )
            ->addOption(
                name: 'approvedByCompany',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The company name who is responsible to approve the sheet.',
            )
            ->addOption(
                name: 'performancePeriod',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The period of the timesheet.',
                default: 'LAST_MONTH',
            )
            ->addOption(
                name: 'billedTo',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The person who will receive the bill. Will fallback to the "approvedByCompany" when not set.',
                default: null,
            )
            ->addOption(
                name: 'startDate',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The start date of the timesheet. Will fallback to today when not set.',
                default: $today,
            )
            ->addOption(
                name: 'approvedAt',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The date when this timesheet was generated. Will fallback to today when not set.',
                default: $today,
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $project = $input->getArgument('project');
        $approvedBy = $input->getOption('approvedBy');
        $approvedByCompany = $input->getOption('approvedByCompany');
        $performancePeriod = $input->getOption('performancePeriod');
        $approvedAt = $input->getOption('approvedAt');
        $startDate = $input->getOption('startDate');
        $billedTo = $input->getOption('billedTo');

        $command = new GenerateTimesheet(
            timesheetId: Uuid::uuid4()->toString(),
            project: $project,
            approvedBy: ['name' => $approvedBy, 'company' => $approvedByCompany],
            providedBy: $this->providedBy,
            performancePeriod: $performancePeriod,
            startDate: $startDate,
            approvedAt: $approvedAt,
            billedTo: $billedTo,
        );

        $this->application->generateTimesheet($command);

        $this->io->success('Timesheet generated.');

        return Command::SUCCESS;
    }
}
