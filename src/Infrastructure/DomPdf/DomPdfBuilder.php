<?php

declare(strict_types=1);

namespace App\Infrastructure\DomPdf;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

class DomPdfBuilder
{
    private Dompdf $domPdf;
    private int $pageNumber = 0;

    private ?string $title = null;

    /**
     * @param list<array{file: string, style: array{family: string, style: string, weight: string}}> $fonts
     */
    public function __construct(
        #[Autowire(param: 'tools.dompdf_creator')]
        private string $creator,
        #[Autowire(param: 'tools.dompdf_producer')]
        private string $producer,
        #[Autowire(param: 'tools.dompdf_tmpDirectory')]
        private string $tmpDirectory,
        #[Autowire(param: 'tools.dompdf_fontsDirectory')]
        private ?string $fontsDirectory = null,
        #[Autowire(param: 'tools.dompdf_fonts')]
        private array $fonts = [],
    ) {
        Assert::allReadable(array_filter([
            $tmpDirectory,
            $fontsDirectory,
        ]));

        $this->domPdf = new Dompdf();
    }

    public function initialize(
        string $title,
    ): self {
        $this->title = $title;

        $cloned = clone $this;

        $options = $this->newOptions();
        $domPdf = new Dompdf($options);

        foreach ($this->fonts as $font) {
            $file = Path::canonicalize($this->fontsDirectory.$font['file']);
            $domPdf->getFontMetrics()
                ->registerFont(
                    style: $font['style'],
                    remoteFile: $file,
                );
        }

        $cloned->domPdf = $domPdf;

        return $cloned;
    }

    public function getPageNumber(string $html): int
    {
        $cloned = clone $this;

        $cloned->domPdf->loadHtml(str: $html, encoding: 'UTF-8');
        $cloned->domPdf->setPaper(size: 'A4');
        $cloned->domPdf->render();

        return $cloned->domPdf->getCanvas()->get_page_count();
    }

    public function load(string $html): self
    {
        $cloned = clone $this;

        $cloned->domPdf->loadHtml(str: $html, encoding: 'UTF-8');
        $cloned->domPdf->setPaper(size: 'A4');
        $cloned->domPdf->render();

        $cloned->pageNumber = $cloned->domPdf->getCanvas()->get_page_count();

        if ($this->pageNumber !== $cloned->pageNumber) {
            // todo consider throwing something like PageNumberChanged -> to let users reprint with the new context
        }

        return $cloned;
    }

    public function build(): string
    {
        $cloned = clone $this;

        $cloned->domPdf->addInfo('Title', $this->title);
        $cloned->domPdf->addInfo('Creator', $this->creator);
        $cloned->domPdf->addInfo('Producer', $this->producer);

        return $cloned->domPdf->output();
    }

    public function newOptions(): Options
    {
        $options = new Options();
        $options->setChroot(
            implode(
                ',',
                array_filter([
                    $this->tmpDirectory,
                    $this->fontsDirectory,
                ]),
            ),
        );

        $options->setTempDir($this->tmpDirectory);

        return $options;
    }
}
