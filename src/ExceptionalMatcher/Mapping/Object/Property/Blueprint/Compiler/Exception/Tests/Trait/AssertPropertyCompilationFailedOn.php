<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\Tests\Trait;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @mixin TestCase
 *
 * @phpstan-require-extends TestCase
 * @psalm-require-extends TestCase
 */
trait AssertPropertyCompilationFailedOn // @phpstan-ignore requireExtends.duplicate
{
    private static function assertPropertyCompilationFailedOn(
        ReflectionProperty $property,
        PropertyExceptionMappingPrintCompilationFailedException $exception,
    ): void {
        self::assertSame($property->class, $exception->getClassName());
        self::assertSame($property->name, $exception->getPropertyName());

        self::assertInstanceOf(CatchExceptionMappingPrintCompilationFailedException::class, $exception->getPrevious());
    }
}
