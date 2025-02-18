<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Model\Component;

interface ComponentRenderer
{
    public function render(Component $component): string;
}
