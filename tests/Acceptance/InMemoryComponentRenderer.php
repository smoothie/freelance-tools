<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Domain\Model\Component;
use App\Domain\Service\ComponentRenderer;
use App\Infrastructure\Twig\TwigComponentRenderer;

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
