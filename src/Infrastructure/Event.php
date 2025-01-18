<?php

declare(strict_types=1);

namespace App\Infrastructure;

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
