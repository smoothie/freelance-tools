<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Doubles;

use Smoothie\FreelanceTools\Tests\Acceptance\InMemoryFilesystem;

final class RecordingFilesystem extends InMemoryFilesystem
{
    /**
     * @var array<string, string>
     */
    private array $latestFiles = [];

    public function dumpExport(string $file, string $content): void
    {
        parent::dumpExport($file, $content);
        $this->latestFiles[$file] = $content;
    }

    public function latestContents(string $file): string
    {
        return $this->latestFiles[$file] ?? throw new \RuntimeException('No exported file: '.$file);
    }
}
