<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\DomPdf;

use Smoothie\FreelanceTools\Application\FilesystemInterface;
use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;
use Smoothie\FreelanceTools\Infrastructure\Twig\TwigComponentRenderer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DomPdfComponentRenderer implements ComponentRenderer
{
    public function __construct(
        #[Autowire(service: TwigComponentRenderer::class)]
        private ComponentRenderer $twig,
        private DomPdfBuilder $domPdfBuilder,
        private FilesystemInterface $filesystem,
    ) {
    }

    public function render(Component $component): string
    {
        $html = $this->twig->render($component);

        $builder = $this->domPdfBuilder->initialize($component->title());

        $component->setPageNumber($builder->getPageNumber($html));

        $html = $this->twig->render($component);

        $pdf = $builder
            ->initialize($component->title())
            ->load($html)
            ->build();

        $this->filesystem->dumpExport(file: $component->fileName(extension: 'pdf'), content: $pdf);

        return $pdf;
    }
}
