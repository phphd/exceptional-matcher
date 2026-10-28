<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\Tests\Trait;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 *
 * @phpstan-require-extends TestCase
 * @psalm-require-extends TestCase
 */
trait AssertCompilationFailedFor // @phpstan-ignore requireExtends.duplicate
{
    /**
     * Asserts the whole compilation failure chain down to the #[Catch_] of the given property.
     *
     * @param class-string $className
     */
    private static function assertCompilationFailedFor(
        ObjectExceptionMappingPrintCompilationFailedException $exception,
        string $className,
        string $propertyName,
    ): void {
        self::assertSame($className, $exception->getClassName());

        $propertyFailure = $exception->getPrevious();
        self::assertInstanceOf(PropertyExceptionMappingPrintCompilationFailedException::class, $propertyFailure);
        self::assertSame($className, $propertyFailure->getClassName());
        self::assertSame($propertyName, $propertyFailure->getPropertyName());

        self::assertInstanceOf(CatchExceptionMappingPrintCompilationFailedException::class, $propertyFailure->getPrevious());
    }
}
