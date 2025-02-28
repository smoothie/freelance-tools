<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Acceptance;

use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;
use Smoothie\FreelanceTools\Infrastructure\Twig\TwigComponentRenderer;

class InMemoryComponentRenderer implements ComponentRenderer
{
    private array $generated = [];

    public function __construct(private TwigComponentRenderer $renderer)
    {
    }

    public function render(Component $component): string
    {
        $html = $this->renderer->render($component);

        $this->generated[] = [
            'template' => $component->template(),
            'title' => $component->title(),
            'context' => $component->context(),
            'rendered' => $html,
        ];

        return $html;
    }

    public function generated(): array
    {
        return $this->generated;
    }

    public function clear(): void
    {
        $this->generated = [];
    }
}
