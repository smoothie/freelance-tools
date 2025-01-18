<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

class WebTestCase extends SymfonyWebTestCase
{
    public function getDoublesDirectory(string $path = ''): string
    {
        return \sprintf('%1$s/Doubles/%2$s', __DIR__, $path);
    }
}
