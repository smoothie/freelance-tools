<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Application\FilesystemInterface;

class InMemoryFilesystem implements FilesystemInterface
{
    protected array $files = [];
    private array $numberOfFiles = [];

    public function dumpExport(string $file, string $content): void
    {
        if (! $this->wasFileDumped($file)) {
            $this->files[$file] = [];
        }

        $this->files[$file][] = $content;
    }

    public function wasFileDumped(string $file): bool
    {
        return \array_key_exists($file, $this->files);
    }

    public function getFileContents(string $file): string
    {
        return $this->files[$file][0];
    }

    public function setNumberOfFiles(string $pattern, int $numberOfFiles): void
    {
        $this->numberOfFiles[$pattern] = $numberOfFiles;
    }

    public function numberOfFilesInExportDirectory(string $pattern): int
    {
        return $this->numberOfFiles[$pattern] ?? 0;
    }

    public function clear(): void
    {
        $this->numberOfFiles = [];
        $this->files = [];
    }
}
