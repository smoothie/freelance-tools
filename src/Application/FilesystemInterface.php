<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

interface FilesystemInterface
{
    public function dumpExport(string $file, string $content): void;

    public function numberOfFilesInExportDirectory(string $pattern): int;
}
