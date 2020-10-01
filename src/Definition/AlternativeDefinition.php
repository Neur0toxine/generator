<?php

namespace ComposerFallback\PackageGenerator\Definition;

class FallbackDefinition
{
    private $name;
    private $definition;

    public function __construct(string $name, array $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
    }

    public function getSubject(): string
    {
        return $this->definition['subject'] ?? '/';
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return AlternativeDefinition
     */
    public function getAlternatives(): iterable
    {
        return $this->name;
    }
}
