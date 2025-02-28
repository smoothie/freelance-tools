<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Twig;

use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;
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
