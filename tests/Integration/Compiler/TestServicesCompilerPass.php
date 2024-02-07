<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Tests\Compiler;

use stdClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class TestServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setParameter('validator.translation_domain', 'test');

        $container->setDefinition('translator', new Definition(stdClass::class));

        $container->getDefinition('phd_exceptional_validation')->setPublic(true);
    }
}
