<?php

declare(strict_types=1);

namespace App\Infrastructure\Toggl;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TogglApiClient
{
    private ?TogglMe $me = null;

    public function __construct(private HttpClientInterface $client)
    {
    }

    public function whoIsMe(): TogglMe
    {
        if ($this->me !== null) {
            return $this->me;
        }

        $response = $this->client->request('GET', 'me', ['query' => ['with_related_data' => true]]);
        $statusCode = $response->getStatusCode();

        $content = $response->toArray(false);

        $me = new TogglMe(
            tags: array_map(static fn (array $item): array => ['id' => $item['id'], 'name' => $item['name']], $content['tags']),
            projects: array_map(
                static fn (array $item): array => [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'client' => $item['client_name'],
                ],
                $content['projects'],
            ),
        );

        $this->me = $me;

        return $me;
    }
}
