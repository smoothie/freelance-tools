<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Infrastructure\Symfony\Filesystem\FilesystemInterface;

class InMemoryFilesystem implements FilesystemInterface
{
    protected array $files = [];

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
}
