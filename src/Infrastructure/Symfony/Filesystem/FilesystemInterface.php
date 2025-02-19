<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Filesystem;

interface FilesystemInterface
{
    public function dumpExport(string $file, string $content): void;
}
