<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\Tests;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
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
            CustomExceptionViolationFormatter::class,
            new Definition(
                CustomExceptionViolationFormatter::class,
                [new Reference(ExceptionViolationFormatter::class.'<Throwable>')],
            ),
        )->setAutoconfigured(true);
    }
}
