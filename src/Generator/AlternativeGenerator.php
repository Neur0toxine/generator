<?php

namespace ComposerFallback\PackageGenerator\Generator;


use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use ComposerFallback\PackageGenerator\Definition\FallbackDefinition;
use ComposerFallback\PackageGenerator\Definition\ImplementationDefinition;
use ComposerFallback\PackageGenerator\Definition\PackageDefinition;
use ComposerFallback\PackageGenerator\Repository\RepositoryWriter;

class PackageGenerator
{
    private $writer;
    private $composer;

    public function __construct(RepositoryWriter $writer, ComposerFileGenerator $composer)
    {
        $this->writer = $writer;
        $this->composer = $composer;
    }

    public function generate(AlternativeDefinition $alternative): void
    {
        $this->initializeRepository($alternative);
        die;
        foreach ($package->getAlternatives() as $alternative) {
            $this->generateAlternative($package, $alternative);
        }

        $this->finalizeRepository($package);
    }

    private function initializeRepository(AlternativeDefinition $alternative)
    {
        $this->writer->clean($alternative);
        $this->writer->init($alternative);
        dump('write readme');
        $this->writer->commit($alternative, 'initial commit');
    }

    private function generateAlternative(AlternativeDefinition $alternative)
    {
        $this->composer->generate($package, $alternative);
        $this->writer->commit($package, sprintf('define composer for "%s"', $alternative->getName()));
        $this->writer->tag($package, '1.0.0');
        if (0 < $priority = $alternative->getPriority()) {
            $this->writer->tag($package, '1.'.$priority.'.0-'.$alternative->getName());
        }
    }

    private function finalizeRepository(PackageDefinition $package)
    {
        $this->composer->generate($package, new PackageDefinition('', []));
        $this->writer->commit($package, 'clean branch master');
        $this->writer->push($package);
    }
}
