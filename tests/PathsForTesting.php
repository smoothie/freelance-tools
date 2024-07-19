<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

interface PathsForTesting
{
    public function getDoublesDirectory(string $path = ''): string;

    public function getTmpDirectory(string $path = ''): string;
}
