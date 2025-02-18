<?php

declare(strict_types=1);

namespace App\Infrastructure\Toggl;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TogglApiClient
{
    private array $cached = [];

    public function __construct(private HttpClientInterface $client)
    {
    }

    public function whoIsMe(): TogglMe
    {
        if (\array_key_exists('me', $this->cached)) {
            return $this->cached['me'];
        }

        $response = $this->client->request('GET', 'me', ['query' => ['with_related_data' => true]]);
        $statusCode = $response->getStatusCode();

        $content = $response->toArray(false);

        $me = new TogglMe(
            tags: array_map(static fn (array $item) => ['id' => $item['id'], 'name' => $item['name']], $content['tags']),
            projects: array_map(
                static fn (array $item) => [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'client' => $item['client_name'],
                ],
                $content['projects'],
            ),
        );

        $this->cached['me'] = $me;

        return $me;
    }
}
