<?php

declare(strict_types=1);

namespace App\Infrastructure\Toggl;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\FilterCriteria;
use App\Domain\Model\Task;
use App\Domain\Model\Timing;
use App\Infrastructure\Doctrine\Lexer\FilterCriteriaQueryParser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

class TogglReportClient
{
    public function __construct(
        private TogglApiClient $togglApi,
        private FilterCriteriaQueryParser $extractor,
        #[Autowire(service: 'tools.toggl_report_client')]
        private HttpClientInterface $reportClient,
    ) {
    }

    /**
     * @return Task[]
     */
    public function findTimeEntries(FilterCriteria $filterCriteria): array
    {
        try {
            $tasks = $this->fetchAndTransform($filterCriteria);
            Assert::allIsInstanceOf($tasks, Task::class);

            return $tasks;
        } catch (TransportExceptionInterface $exception) {
        } catch (DecodingExceptionInterface $exception) {
        } catch (HttpExceptionInterface $exception) {
        }
        // TODO handle not so good cases

        return [];
    }

    private function fetchAndTransform(FilterCriteria $filterCriteria): array
    {
        $expressions = $this->extractor->parse($filterCriteria);
        $allowed = [
            'project' => $expressions->project()->value(),
            'client' => $expressions->client()->value(),
        ];
        $data = [
            'grouped' => true,
            'first_row_number' => 1,
            'start_date' => DateTime::fromDateString($expressions->startDate()->value())->asPhpDateTime()->format('Y-m-d'),
            'end_date' => DateTime::fromDateString($expressions->endDate()->value())->asPhpDateTime()->format('Y-m-d'),
        ];

        $me = $this->togglApi->whoIsMe();
        $firstRowNumber = 1;
        /** @var Task[] $tasks */
        $tasks = [];
        $projectMap = $me->projectMap();
        $clientMap = $me->clientMap();
        $tagMap = $me->tagMap();

        while ($firstRowNumber > 0) {
            $data['first_row_number'] = $firstRowNumber;

            $response = $this->reportClient->request('POST', 'search/time_entries', ['json' => $data]);
            $content = $response->toArray();
            foreach ($content as $item) {
                $task = new Task(
                    projectId: $projectMap[$item['project_id']],
                    clientId: $clientMap[$item['project_id']],
                    description: $item['description'],
                    tags: array_map(static fn (string $tagId) => $tagMap[$tagId], $item['tag_ids']),
                    timings: array_map(static fn (array $data) => new Timing(
                        startTime: (\DateTimeImmutable::createFromFormat(\DateTimeImmutable::RFC3339, $data['start']))
                            ->format('Y-m-d H:i:s'),
                        endTime: (\DateTimeImmutable::createFromFormat(\DateTimeImmutable::RFC3339, $data['stop']))
                            ->format('Y-m-d H:i:s'),
                    ), $item['time_entries']),
                );

                $tasks[] = $task;
            }

            $firstRowNumber = $this->identifyNextRowNumber($response);
        }

        // TODO: Consider to extract the filter out and applying it on domain level
        $tasks = array_filter($tasks, static function (Task $task) use ($allowed) {
            if ($allowed['project'] === null) {
                return true;
            }

            if (\is_array($allowed['project'])) {
                return \in_array($task->projectId()->asString(), $allowed['project'], true);
            }

            return $task->projectId()->asString() === $allowed['project'];
        });

        $tasks = array_filter($tasks, static function (Task $task) use ($allowed) {
            if ($allowed['client'] === null) {
                return true;
            }

            if (\is_array($allowed['client'])) {
                return \in_array($task->clientId()->asString(), $allowed['client'], true);
            }

            return $task->clientId()->asString() === $allowed['client'];
        });

        return array_values($tasks);
    }

    private function identifyNextRowNumber(ResponseInterface $response): ?int
    {
        $headers = $response->getHeaders();
        if (! \array_key_exists('x-next-row-number', $headers)) {
            return null;
        }

        return (int) $headers['x-next-row-number'][0];
    }
}
