<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\Tests;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub\CustomExceptionFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\PropriatedExceptionFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class TestServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->registerCustomViolationFormatter($container);
    }

    private function registerCustomViolationFormatter(ContainerBuilder $container): void
    {
        $container->setDefinition(
            CustomExceptionFormatter::class,
            new Definition(
                CustomExceptionFormatter::class,
                [new Reference(PropriatedExceptionFormatter::class.'<Throwable>')],
            ),
        )->setAutoconfigured(true);
    }
}
