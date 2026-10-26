<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Symfony\Command;

use Smoothie\FreelanceTools\Application\BillingDocuments;
use Smoothie\FreelanceTools\Infrastructure\Symfony\BillingDocumentInput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'tools:generate-document', description: 'Generate a billing document from YAML or JSON')]
final class GenerateBillingDocumentCommand extends Command
{
    public function __construct(
        private BillingDocuments $documents,
        private BillingDocumentInput $parser,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'Path to a .yaml, .yml or .json document; use - for standard input.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Override documentType: invoice, advance, final or quote.')
            ->addOption('template', null, InputOption::VALUE_REQUIRED, 'Override templateId: classic or marc.')
            ->addOption('number', null, InputOption::VALUE_REQUIRED, 'Override the document number.')
            ->setHelp('Provide --input=path.yaml, --input=path.json or --input=-. Quote dates and decimal values in YAML. No interactive input is supported.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $data = $this->load($input);
            foreach (['type' => 'documentType', 'template' => 'templateId', 'number' => 'number'] as $option => $key) {
                $override = $input->getOption($option);
                if ($override !== null) {
                    $data[$key] = $override;
                }
            }
            $request = $this->parser->parse($data);
        } catch (\InvalidArgumentException|\JsonException|ParseException $exception) {
            $io->error($exception->getMessage());

            return self::INVALID;
        }

        try {
            $document = $this->documents->generate($request);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return self::INVALID;
        }

        $io->success(\sprintf('Document %s generated: %s', $document->invoiceId()->asString(), $document->fileName('pdf')));

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function load(InputInterface $input): array
    {
        $path = $input->getOption('input');
        if (! \is_string($path) || $path === '') {
            throw new \InvalidArgumentException('Provide --input=path.yaml, --input=path.json or --input=- for standard input.');
        }
        $extension = strtolower(pathinfo($path, \PATHINFO_EXTENSION));
        if ($path !== '-' && ! \in_array($extension, ['yaml', 'yml', 'json'], true)) {
            throw new \InvalidArgumentException('Input must be a .yaml, .yml or .json file, or - for standard input.');
        }
        if ($path === '-') {
            $stream = $input instanceof StreamableInputInterface ? $input->getStream() : null;
            $content = $stream === null ? @file_get_contents('php://stdin') : @stream_get_contents($stream);
        } else {
            if (! is_file($path) || ! is_readable($path)) {
                throw new \InvalidArgumentException('Cannot read input file: '.$path);
            }
            $content = @file_get_contents($path);
        }
        if ($content === false) {
            throw new \InvalidArgumentException('Cannot read input: '.$path);
        }
        $json = $extension === 'json' || ($path === '-' && \in_array(substr(ltrim($content), 0, 1), ['{', '['], true));
        $data = $json ? json_decode($content, true, 512, \JSON_THROW_ON_ERROR) : Yaml::parse($content, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        if (! \is_array($data) || array_is_list($data)) {
            throw new \InvalidArgumentException('Input must contain a document mapping.');
        }
        foreach (array_keys($data) as $key) {
            if (! \is_string($key)) {
                throw new \InvalidArgumentException('Document keys must be strings.');
            }
        }

        return $data;
    }
}
