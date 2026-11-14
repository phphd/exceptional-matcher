<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Cart;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/** @internal */
final class OutOfStockExceptionMatchFormatterCompilerPass implements CompilerPassInterface
{
    /** Must outrank {@see ResolveInstanceofConditionalsPass} (100) for interface autoconfiguration to work */
    public const PRIORITY = 105;

    public function process(ContainerBuilder $container): void
    {
        $container->setAlias(
            ExceptionMatchFormatter::class.'<'.OutOfStockProductException::class.','.ConstraintViolationInterface::class.'>',
            OutOfStockExceptionMatchFormatter::class,
        );

        $container->setDefinition(
            OutOfStockExceptionMatchFormatter::class,
            new Definition(
                OutOfStockExceptionMatchFormatter::class,
                [new Reference(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>')],
            ),
        )->setAutoconfigured(autoconfigured: true);
    }
}
