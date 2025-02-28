<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure;

class Event
{
    public static function asString(object $event): string
    {
        if (method_exists($event, '__toString')) {
            return (string) $event;
        }

        return $event::class;
    }
}
