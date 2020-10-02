<?php

namespace ComposerFallback\PackageGenerator\Definition;

class AlternativeDefinition
{
    private const VENDOR = 'composer-fallback';

    private $fallback;
    private $name;
    private $definition;

    public function __construct(FallbackDefinition $fallback, string $name, array $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
        $this->fallback = $fallback;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPackageFullName(): string
    {
        return sprintf("%s/%s", self::VENDOR, $this->getPackageName());
    }

    public function getPackageName(): string
    {
        return sprintf("%s.%s", \str_replace(['.', '/', ' '], ['', '.', '-'], $this->fallback->getName()), $this->name);
    }

    public function getFallback(): FallbackDefinition
    {
        return $this->fallback;
    }

    public function getPackage(): PackageDefinition
    {
        return new PackageDefinition($this->definition);
    }
}
