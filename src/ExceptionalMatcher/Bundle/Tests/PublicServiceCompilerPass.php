<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle\Tests;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @internal */
final class PublicServiceCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $serviceId,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition($this->serviceId)
            ->setPublic(true)
        ;
    }
}
