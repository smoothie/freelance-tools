<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime\Credentials;

enum AuthorizationType: string
{
    case NONE = 'NONE';
    case API_KEY = 'API_KEY';
    case BASIC_AUTH = 'BASIC_AUTH';
    case BEARER_TOKEN = 'BEARER_TOKEN';
}
