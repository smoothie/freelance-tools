<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Toggl;

use Webmozart\Assert\Assert;

class TogglMe
{
    /**
     * @param list<array{id: int, name: string}> $tags
     * @param list<array{id: int, name: string, client: string}> $projects
     */
    public function __construct(
        private array $tags,
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

    /**
     * @return list<array{id: int, name: string}>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @return list<array{id: int, name: string, client: string}>
     */
    public function projects(): array
    {
        return $this->projects;
    }

    /**
     * @return array<int, string>
     */
    public function tagMap(): array
    {
        $result = [];
        foreach ($this->tags() as $tag) {
            $result[$tag['id']] = $tag['name'];
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public function projectMap(): array
    {
        $result = [];
        foreach ($this->projects() as $project) {
            $result[$project['id']] = $project['name'];
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public function clientMap(): array
    {
        $result = [];
        foreach ($this->projects() as $project) {
            $result[$project['id']] = $project['client'];
        }

        return $result;
    }
}
