<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\Tests\Trait;

use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @mixin TestCase
 *
 * @phpstan-require-extends TestCase
 * @psalm-require-extends TestCase
 */
trait RootCompilationException // @phpstan-ignore requireExtends.duplicate
{
    /**
     * Asserts the whole compilation failure chain and returns the condition error that started it.
     *
     * @param class-string $className
     */
    private static function rootCompilationException(
        ObjectExceptionMappingPlanCompilationFailedException $exception,
        string $className,
        string $propertyName,
    ): Throwable {
        self::assertSame($className, $exception->getClassName());

        $propertyFailure = $exception->getPrevious();
        self::assertInstanceOf(PropertyExceptionMappingPlanCompilationFailedException::class, $propertyFailure);
        self::assertSame($propertyName, $propertyFailure->getPropertyName());

        $catchFailure = $propertyFailure->getPrevious();
        self::assertInstanceOf(CatchExceptionMappingPlanCompilationFailedException::class, $catchFailure);

        $rootCause = $catchFailure->getPrevious();
        self::assertNotNull($rootCause);

        return $rootCause;
    }
}
