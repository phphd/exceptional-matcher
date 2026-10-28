<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\Tests\Trait;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
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
        ObjectExceptionMappingPlanCompilationFailedException $exception,
        string $className,
        string $propertyName,
    ): void {
        self::assertSame($className, $exception->getClassName());

        $propertyFailure = $exception->getPrevious();
        self::assertInstanceOf(PropertyExceptionMappingPlanCompilationFailedException::class, $propertyFailure);
        self::assertSame($className, $propertyFailure->getClassName());
        self::assertSame($propertyName, $propertyFailure->getPropertyName());

        self::assertInstanceOf(CatchExceptionMappingPlanCompilationFailedException::class, $propertyFailure->getPrevious());
    }
}
