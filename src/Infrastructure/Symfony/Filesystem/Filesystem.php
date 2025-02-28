<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Filesystem;

use App\Application\FilesystemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

class Filesystem implements FilesystemInterface
{
    public function __construct(
        private SymfonyFilesystem $filesystem,
        #[Autowire(param: 'tools.dompdf_exportDirectory')]
        private ?string $exportDirectory = null,
    ) {
    }

    public function dumpExport(string $file, string $content): void
    {
        $path = Path::canonicalize(\sprintf('%s/%s', $this->exportDirectory, $file));

        $this->filesystem->dumpFile($path, $content);
    }

    public function numberOfFilesInExportDirectory(string $pattern): int
    {
        $finder = new Finder();
        $files = $finder->in([$this->exportDirectory])->name($pattern);

        return $files->count();
    }
}
