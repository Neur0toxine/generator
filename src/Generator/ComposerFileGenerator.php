<?php

namespace ComposerFallback\PackageGenerator\Generator;

use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use ComposerFallback\PackageGenerator\Definition\PackageDefinition;
use ComposerFallback\PackageGenerator\Repository\RepositoryWriter;

class ComposerFileGenerator
{
    public const VENDOR = 'Neur0toxine';

    private $writer;

    public function __construct(RepositoryWriter $writer)
    {
        $this->writer = $writer;
    }

    public function generate(AlternativeDefinition $alternative, PackageDefinition $package): void
    {
        $this->writer->writeFile($alternative, 'composer.json', \json_encode([
            'name' => $alternative->getPackageFullName(),
            'description' => 'Satisfy "'.$alternative->getFallback()->getName().'" with packages provided by user, or fallback to "'.$alternative->getName().'".',
            'type' => 'metapackage',
            'license' => 'MIT',
            'keywords' => \array_merge([
                'composer',
                'fallback',
                'default',
            ], \explode('/', $alternative->getFallback()->getName())),
            'require' => $package->getRequirements()
        ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}
