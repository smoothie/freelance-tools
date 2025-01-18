<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class BasicTestCase extends TestCase
{
    public function getDoublesDirectory(string $path = ''): string
    {
        return \sprintf('%1$s/Doubles/%2$s', __DIR__, $path);
    }

    public function getTmpDirectory(string $path = ''): string
    {
        return sys_get_temp_dir().$path;
    }

    /**
     * @param array<object> $objects
     */
    protected static function assertArrayContainsObjectOfType(string $expectedClass, array $objects): void
    {
        $objectsOfExpectedType = array_filter(
            $objects,
            static function ($object) use ($expectedClass) {
                return $object instanceof $expectedClass;
            });

        self::assertNotEmpty($objectsOfExpectedType, 'Expected array to contain object of type '.$expectedClass);
    }
}
