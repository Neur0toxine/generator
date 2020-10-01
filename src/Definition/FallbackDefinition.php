<?php

namespace ComposerFallback\PackageGenerator\Definition;

class PackageDefinition
{
    private $name;
    private $definition;

    public function __construct(string $name, array $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->definition['priority'] ?? 0;
    }

    /**
     * @return array<int, string>
     */
    public function getRequirements(): array
    {
        return $this->definition['requirements'] ?? [];
    }
}
