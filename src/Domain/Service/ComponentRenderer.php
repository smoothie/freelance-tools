<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Service;

use Smoothie\FreelanceTools\Domain\Model\Component;

interface ComponentRenderer
{
    public function render(Component $component): string;
}
