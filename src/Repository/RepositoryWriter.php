<?php

namespace ComposerFallback\PackageGenerator\Repository;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Repository
{
    private $basePath;
    private $fileSystem;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->fileSystem = new Filesystem();
    }

    public function clean(): void
    {
        $this->fileSystem->remove($this->basePath);
        $this->fileSystem->mkdir($this->basePath, 0775);
    }

    public function init(): void
    {
        new Process(['git', 'init', $this->basePath]);
    }
}
