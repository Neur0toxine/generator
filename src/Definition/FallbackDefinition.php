<?php

namespace ComposerFallback\PackageGenerator\Definition;

class FallbackDefinition
{
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_POLYFILL = 'polyfill';
    public const TYPE_VIRTUAL = 'virtual';
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

    public function getType(): string
    {
        return $this->definition['type'] ?? self::TYPE_UNKNOWN;
    }

    public function getPreferredPackage(): PackageDefinition
    {
        return new PackageDefinition($this->definition['preferred']);
    }

    /**
     * @return AlternativeDefinition
     */
    public function getAlternatives(): iterable
    {
        foreach ($this->definition['alternatives'] ?? [] as $name => $definition) {
            yield $name => new AlternativeDefinition($this, $name, $definition);
        }
    }
}
