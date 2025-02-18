<?php

declare(strict_types=1);

namespace App\Infrastructure\DomPdf;

use App\Domain\Model\Component;
use App\Domain\Service\ComponentRenderer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class DomPdfComponentRenderer implements ComponentRenderer
{
    public function __construct(
        private Environment $twig,
        private DomPdfBuilder $domPdfBuilder,
        #[Autowire(param: 'tools.dompdf_templateDirectory')]
        private ?string $templateDirectory = null,
    ) {
    }

    public function render(Component $component): string
    {
        $context = $component->context();

        $html = $this->twig->render($component->template(), $context);

        $builder = $this->domPdfBuilder->initialize($component->title());

        $context['last_page_number'] = $builder->getPageNumber($html);

        $html = $this->twig->render($component->template(), $context);

        return $builder
            ->load($html)
            ->build();
    }
}
