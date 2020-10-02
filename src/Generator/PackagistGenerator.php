<?php

namespace ComposerFallback\PackageGenerator\Generator;


use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class PackagistGenerator
{
    private $logger;
    private $client;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = HttpClient::create();
        $this->logger = $logger;
    }

    public function generate(AlternativeDefinition $alternative): void
    {
        if ($this->client->request('GET', 'https://repo.packagist.org/p2/'.$alternative->getPackageFullName().'.json')->getStatusCode() === 404) {
            $this->logger->warning('package not declared in composer: https://github.com/'. $alternative->getPackageFullName());
        }
    }
}
