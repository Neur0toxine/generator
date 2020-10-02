<?php

namespace ComposerFallback\PackageGenerator\Definition;

class PackageDefinition
{
    private $definition;

    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return array<int, string>
     */
    public function getRequirements(): array
    {
        return $this->definition['requirements'] ?? [];
    }
}
