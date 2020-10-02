<?php

namespace ComposerFallback\PackageGenerator\Repository;

use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RepositoryWriter
{
    private $basePath;
    private $logger;
    private $fileSystem;

    public function __construct(string $basePath, LoggerInterface $logger)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->logger = $logger;

        $this->fileSystem = new Filesystem();
    }

    public function clean(AlternativeDefinition $alternative): void
    {
        $this->fileSystem->remove($dir = $this->getPackageDirectory($alternative));
        $this->fileSystem->mkdir($dir, 0775);
    }

    public function init(AlternativeDefinition $alternative): void
    {
        $this->run(['git', 'init', '.'], $alternative);
    }

    public function writeFile(AlternativeDefinition $alternative, string $path, string $content): void
    {
        $this->fileSystem->dumpFile($this->getPackageDirectory($alternative).'/'.$path, $content);
        $this->run(['git', 'add', $path], $alternative);
    }

    public function commit(AlternativeDefinition $alternative, string $message): void
    {
        $this->run(['git', 'commit', '--allow-empty', '-m', $message], $alternative);
    }

    public function tag(AlternativeDefinition $alternative, string $tag): void
    {
        $this->run(['git', 'tag', $tag], $alternative);
    }

    public function push(AlternativeDefinition $alternative): void
    {
        try {
            $this->run([
                'gh', 'repo', 'create',
                $alternative->getPackageFullName(),
                '--description=[READ ONLY] Fallback implementation for ' . $alternative->getFallback()->getName(),
                '--enable-issues=false', '--enable-wiki=false', '--public', '-y'], $alternative);
        } catch (ProcessFailedException $e) {
            if (\strpos($e->getProcess()->getErrorOutput(), 'Name already exists on this account') === false) {
                throw $e;
            }
            $this->run(['git', 'remote', 'add', 'origin', sprintf('git@github.com:%s.git', $alternative->getPackageFullName())], $alternative);
        }
        $this->run(['git', 'push', 'origin', '-f', '--tags'], $alternative);
    }

    private function deletePreviousTags(AlternativeDefinition $alternative): void
    {
        $output = $this->run(['git', 'ls-remote', '--tags', '-q', '--refs'], $alternative)->getOutput();
        $tags = [];
        foreach (\explode(PHP_EOL, $output) as $tag) {
            if (!$tag) {
                continue;
            }
            [$sha, $ref] = explode("\t", $tag);
            $tags[] = substr($ref, \strlen('refs/tags/'));
        }
        if (!empty($tags)) {
            $this->run(\array_merge(['git', 'push', 'origin', '--delete'], $tags), $alternative);
        }
    }

    private function run(array $command, AlternativeDefinition $alternative = null): Process
    {
        $this->logger->debug('> '.\implode(' ', $command));
        return (new Process($command, $alternative ? $this->getPackageDirectory($alternative) : null))->mustRun();
    }

    private function getPackageDirectory(AlternativeDefinition $alternative): string
    {
        return sprintf("%s/%s", $this->basePath, $alternative->getPackageName());
    }
}
