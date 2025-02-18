<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Domain\Model\Component;
use App\Domain\Service\ComponentRenderer;
use Twig\Environment;

class TwigComponentRenderer implements ComponentRenderer
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function render(Component $component): string
    {
        $html = $this->twig->render(
            name: $component->template(),
            context: $component->context(),
        );

        return $html;
    }
}
