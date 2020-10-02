<?php

namespace ComposerFallback\PackageGenerator\Generator;

use ComposerFallback\PackageGenerator\Repository\RepositoryWriter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Generator
{
    private $basePath;
    private $logger;

    private $alternative;
    private $repository;
    private $composerFile;
    private $readmeFile;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function alternative(): AlternativeGenerator
    {
        return $this->alternative ?? $this->alternative = new AlternativeGenerator(
                $this->repository(),
                $this->composerFile(),
                $this->readmeFile(),
                $this->logger
            );
    }

    private function repository(): RepositoryWriter
    {
        return $this->repository ?? $this->repository = new RepositoryWriter($this->basePath, $this->logger);
    }

    private function composerFile(): ComposerFileGenerator
    {
        return $this->composerFile ?? $this->composerFile = new ComposerFileGenerator($this->repository());
    }

    private function readmeFile(): ReadmeFileGenerator
    {
        return $this->readmeFile ?? $this->readmeFile = new ReadmeFileGenerator($this->repository());
    }

    public function packagist(): PackagistGenerator
    {
        return $this->packagist ?? $this->packagist = new PackagistGenerator($this->logger);
    }
}
