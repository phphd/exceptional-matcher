<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Bundle\Tests;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Tests\Stub\CustomExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/** @internal */
final class RegisterCustomViolationFormatterCompilerPass implements CompilerPassInterface
{
    /** Must outrank {@see ResolveInstanceofConditionalsPass} (100) for interface autoconfiguration to work */
    public const PRIORITY = 105;

    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            CustomExceptionViolationFormatter::class,
            new Definition(
                CustomExceptionViolationFormatter::class,
                [new Reference(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>')],
            ),
        )->setAutoconfigured(autoconfigured: true);
    }
}
