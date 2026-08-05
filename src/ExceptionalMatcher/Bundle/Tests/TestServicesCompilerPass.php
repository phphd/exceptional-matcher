<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Bundle\Tests;

use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/** @internal */
final class TestServicesCompilerPass implements CompilerPassInterface
{
    /** Must outrank {@see ResolveInstanceofConditionalsPass} (100) for interface autoconfiguration to work */
    public const PRIORITY = 105;

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
