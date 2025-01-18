<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\WorkTime;

use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Task\ListOfTasks;
use App\Domain\Models\Task\Task;
use App\Domain\Models\Task\TaskId;
use App\Domain\Models\Timing\TimingId;
use App\Domain\Models\WorkTime\Credentials\AuthorizationType;
use App\Domain\Models\WorkTime\Credentials\Credentials;
use App\Domain\Models\WorkTime\WorkTimeProcessor;
use App\Domain\Models\WorkTime\WorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProviderId;
use App\Domain\Models\WorkTime\WorkTimeProviderType;
use Ramsey\Uuid\Uuid;

trait WorkTimeFactoryMethods
{
    protected function aWorkTimeProviderId(): WorkTimeProviderId
    {
        return WorkTimeProviderId::fromString('0dbc4e01-0000-0000-0000-000000000000');
    }

    protected function aWorkTimeProviderType(): WorkTimeProviderType
    {
        return WorkTimeProviderType::CSV;
    }

    protected function aCredentials(): Credentials
    {
        return new class implements Credentials {
            public function authType(): AuthorizationType
            {
                return AuthorizationType::NONE;
            }

            public function key(): string
            {
                return 'A_KEY';
            }

            public function value(): string
            {
                return 'A_VALUE';
            }
        };
    }

    protected function aWorkTimeProcessor(): WorkTimeProcessor
    {
        $listOfTasks = $this->aRandomListOfTasks();

        return new class($listOfTasks) implements WorkTimeProcessor {
            public function __construct(private ListOfTasks $listOfTasks)
            {
            }

            public function getListOfTasks(FilterCriteria $filter, Credentials $credentials): ListOfTasks
            {
                return $this->listOfTasks;
            }
        };
    }

    protected function aWorkTimeFilterCriteria(): FilterCriteria
    {
        return FilterCriteria::fromString('project = a');
    }

    protected function aWorkTimeProvider(): WorkTimeProvider
    {
        $workTimeProvider = WorkTimeProvider::setup(
            workTimeProviderId: $this->aWorkTimeProviderId(),
            workTimeProviderType: $this->aWorkTimeProviderType(),
            credentials: $this->aCredentials(),
        );

        $workTimeProvider->releaseEvents();

        return $workTimeProvider;
    }

    protected function aRandomTaskId(): TaskId
    {
        return TaskId::fromString(Uuid::uuid4()->toString());
    }

    protected function aRandomProjectId(): ProjectId
    {
        return ProjectId::fromString(Uuid::uuid4()->toString());
    }

    protected function aRandomTimingId(): TimingId
    {
        return TimingId::fromString(Uuid::uuid4()->toString());
    }

    protected function aDescription(): string
    {
        return 'A description';
    }

    protected function anImportedAt(): string
    {
        return '2024-12-31 11:00:00';
    }

    protected function aLastModifiedAt(): string
    {
        return '2024-12-31 12:00:00';
    }

    protected function aRandomTask(): Task
    {
        return Task::import(
            $this->aRandomTaskId(),
            $this->aRandomProjectId(),
            $this->aDescription(),
            $this->anImportedAt(),
            $this->aLastModifiedAt(),
        );
    }

    protected function aRandomListOfTasks(int $amountOfTasks = 10): ListOfTasks
    {
        $tasks = [];
        foreach (range(1, $amountOfTasks) as $i) {
            $tasks[] = $this->aRandomTask();
        }

        return new ListOfTasks($tasks);
    }
}
