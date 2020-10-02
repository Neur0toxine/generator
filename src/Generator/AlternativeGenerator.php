<?php

namespace ComposerFallback\PackageGenerator\Generator;

use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use ComposerFallback\PackageGenerator\Repository\RepositoryWriter;
use Psr\Log\LoggerInterface;

class AlternativeGenerator
{
    private $writer;
    private $composer;
    private $readme;
    private $logger;

    public function __construct(
        RepositoryWriter $writer,
        ComposerFileGenerator $composer,
        ReadmeFileGenerator $readme,
        LoggerInterface $logger
    ) {
        $this->writer = $writer;
        $this->composer = $composer;
        $this->readme = $readme;
        $this->logger = $logger;
    }

    public function generate(AlternativeDefinition $alternative): void
    {
        $this->logger->info('Generating repository');
        $this->initializeRepository($alternative);

        $this->composer->generate($alternative, $alternative->getPackage());
        $this->writer->commit($alternative, sprintf('define composer for fallback "%s"', $alternative->getName()));
        $this->writer->tag($alternative, '1.0');

        $this->composer->generate($alternative, $alternative->getFallback()->getPreferredPackage());
        $this->writer->commit($alternative, sprintf('define composer for preferred requirements'));
        $this->writer->tag($alternative, '1.1');

        $this->logger->info('Pushing changes');
        $this->finalizeRepository($alternative);
    }

    private function initializeRepository(AlternativeDefinition $alternative)
    {
        $this->writer->clean($alternative);
        $this->writer->init($alternative);
        $this->readme->generate($alternative);
        $this->writer->commit($alternative, 'initial commit');
    }

    private function finalizeRepository(AlternativeDefinition $alternative)
    {
        $this->writer->push($alternative);
    }
}
