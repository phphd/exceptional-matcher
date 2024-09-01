<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\Tests\Compiler;

use PhPhD\ExceptionalValidation\Tests\Unit\Stub\CustomExceptionViolationFormatter;
use stdClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class TestServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('phd_exceptional_validation')->setPublic(true);

        $this->registerCustomViolationFormatter($container);

        $this->registerTranslator($container);
    }

    private function registerCustomViolationFormatter(ContainerBuilder $container): void
    {
        $container->setDefinition(
            CustomExceptionViolationFormatter::class,
            new Definition(
                CustomExceptionViolationFormatter::class,
                [new Reference('phd_exceptional_validation.violation_formatter.default')],
            ),
        )->setAutoconfigured(true);
    }

    private function registerTranslator(ContainerBuilder $container): void
    {
        $container->setParameter('validator.translation_domain', 'test');

        $container->setDefinition('translator', (new Definition(stdClass::class))->setPublic(true));
    }
}
