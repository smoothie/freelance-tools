<?php

declare(strict_types=1);

namespace App\Infrastructure\Toggl;

use Webmozart\Assert\Assert;

class TogglMe
{
    public function __construct(
        /** @var list<array{id: int, name: string}> */
        private array $tags,
        /** @var list<array{id: int, name: string, client: string}> */
        private array $projects,
    ) {
        foreach ($tags as $tag) {
            Assert::keyExists($tag, 'id');
            Assert::keyExists($tag, 'name');
            Assert::allNotEmpty($tag);
        }

        foreach ($projects as $project) {
            Assert::keyExists($project, 'id');
            Assert::keyExists($project, 'name');
            Assert::keyExists($project, 'client');
            Assert::allNotEmpty($project);
        }
    }

    public function tags(): array
    {
        return $this->tags;
    }

    public function projects(): array
    {
        return $this->projects;
    }

    public function tagMap(): array
    {
        $result = [];
        foreach ($this->tags as $tag) {
            $result[$tag['id']] = $tag['name'];
        }

        return $result;
    }

    public function projectMap(): array
    {
        $result = [];
        foreach ($this->projects as $project) {
            $result[$project['id']] = $project['name'];
        }

        return $result;
    }

    public function clientMap(): array
    {
        $result = [];
        foreach ($this->projects as $project) {
            $result[$project['id']] = $project['client'];
        }

        return $result;
    }
}
