<?php

declare(strict_types=1);

namespace App\Application;

interface FilesystemInterface
{
    public function dumpExport(string $file, string $content): void;

    public function numberOfFilesInExportDirectory(string $pattern): int;
}
