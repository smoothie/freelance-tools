<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

trait ProvidesPathsForTesting
{
    public function getDoublesDirectory(string $path = ''): string
    {
        return sprintf('%1$s/Doubles/%2$s', __DIR__, $path);
    }

    public function getTmpDirectory(string $path = ''): string
    {
        return sys_get_temp_dir().$path;
    }
}
