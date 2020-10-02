<?php

declare(strict_types=1);

namespace ComposerFallback\PackageGenerator\Command;

use ComposerFallback\PackageGenerator\Definition\FallbackDefinition;
use ComposerFallback\PackageGenerator\Generator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate repositories for fallback packages
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';

    private $manifestFile;
    private $generator;

    public function __construct(string $manifestFile, Generator $generator)
    {
        $this->manifestFile = $manifestFile;
        $this->generator = $generator;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setAliases(['update']);
        $this->setDescription('Create or update packages.');
        $this->setDefinition(
            [
                new InputArgument('package', InputArgument::OPTIONAL),
                new InputOption('all', null, InputOption::VALUE_NONE, 'Update all packages'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $this->generator->setLogger(new ConsoleLogger($style));
        $manifest = $this->loadManifest();
        $alternativeGenerator = $this->generator->alternative();
        $packagistGenerator = $this->generator->packagist();
        foreach ($manifest['fallbacks'] ?? [] as $name => $definition) {
            $fallback = new FallbackDefinition($name, $definition);
            foreach ($fallback->getAlternatives() as $alternative) {
                $style->block('Generating '.$alternative->getName().' for '.$fallback->getName());
                $alternativeGenerator->generate($alternative);
                $packagistGenerator->generate($alternative);
            }
        }

        return Command::SUCCESS;
    }

    private function loadManifest(): array
    {
        return \json_decode(\file_get_contents($this->manifestFile), true);
    }
}
