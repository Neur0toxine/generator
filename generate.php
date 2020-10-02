<?php

use ComposerFallback\PackageGenerator\Command\GenerateCommand;
use ComposerFallback\PackageGenerator\Generator\Generator;
use Symfony\Component\Console\Application;

$application = new Application('AsyncAws', '0.1.0');

$src = $_SERVER['COMPOSER_FALLBACK_GENERATE_SRC'] ?? __DIR__ . '/../build/';
$manifest = $_SERVER['COMPOSER_FALLBACK_GENERATE_MANIFEST'] ?? __DIR__ . '/manifest.json';

$command = new GenerateCommand($manifest, new Generator($src));
$application->add($command);
$application->setDefaultCommand($command->getName(), true);

$application->run();
