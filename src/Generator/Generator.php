<?php

namespace ComposerFallback\PackageGenerator\Generator;


use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use ComposerFallback\PackageGenerator\Definition\PackageDefinition;
use ComposerFallback\PackageGenerator\Repository\Writer;

class PackageGenerator
{
    private $srcDirectory;
    private $writer;

    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    public function generate(PackageDefinition $package): void
    {
        $this->initializeRepository($package);
        foreach ($package->getAlternatives() as $alternative) {
            $this->generateAlternative($alternative);
        }

        $this->finalizeRepository($package);
    }

    private function initializeRepository(PackageDefinition $package)
    {
        dump('mkdir '.$this->srcDirectory.'/'.$package->getName());
        dump('git init .');
        dump('composer init');
        dump('write readme');
        dump('git commit');
    }

    private function generateAlternative(AlternativeDefinition $alternative)
    {
        dump('write composer for '.$alternative->getName());
        dump('git commit');
        dump('git tag 1.0-'.$alternative->getName());
        if (0 < $priority = $alternative->getPriority()) {
            dump('git tag 1.'.$priority.'-'.$alternative->getName());
        }
    }

    private function finalizeRepository()
    {
        dump('reset composer');
        dump('git commit');
        dump('git push --all');
    }
}
