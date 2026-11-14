<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Message;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Throwable;

/** @internal */
final class ExceptionMessageFormatterCompilerPass implements CompilerPassInterface
{
    /** Must outrank {@see ResolveInstanceofConditionalsPass} (100) for interface autoconfiguration to work */
    public const PRIORITY = 105;

    public function process(ContainerBuilder $container): void
    {
        $container->setAlias(
            ExceptionMatchFormatter::class.'<'.Throwable::class.',string>',
            ExceptionMessageFormatter::class,
        );

        $container->setDefinition(
            ExceptionMessageFormatter::class,
            new Definition(ExceptionMessageFormatter::class),
        )->setAutoconfigured(autoconfigured: true);
    }
}
