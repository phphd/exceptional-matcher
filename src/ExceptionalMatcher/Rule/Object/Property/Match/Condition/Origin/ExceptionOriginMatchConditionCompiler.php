<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\PreCompiledMatchConditionBlueprint;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

use function count;
use function explode;
use function function_exists;
use function property_exists;
use function sprintf;
use function str_starts_with;
use function substr;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class ExceptionOriginMatchConditionCompiler implements MatchConditionCompiler
{
    /** @return ?PreCompiledMatchConditionBlueprint<Throwable> */
    public function compile(Catch_ $catch): ?PreCompiledMatchConditionBlueprint
    {
        /** @var ?non-empty-list<?string> $origin */
        $origin = $catch->getFrom();

        if (null === $origin) {
            return null;
        }

        @[$originClassName, $originFunctionName] = $origin;

        if (null !== $originClassName) {
            Assert::classExists($originClassName);

            if (null !== $originFunctionName) {
                self::assertClassMethodExists($originClassName, $originFunctionName);
            }
        } elseif (null !== $originFunctionName) {
            Assert::true(function_exists($originFunctionName));
        } else {
            throw new LogicException('At least one of the originClassName or originFunctionName must be set.');
        }

        /** @psalm-var ?non-empty-string $originFunctionName */
        $condition = new ExceptionOriginMatchCondition($originClassName, $originFunctionName);

        return new PreCompiledMatchConditionBlueprint($condition);
    }

    /**
     * @param class-string $originClassName
     *
     * @phpstan-assert non-empty-string $originFunctionName
     */
    private static function assertClassMethodExists(string $originClassName, string $originFunctionName): void
    {
        if (self::referencesPropertyHook($originFunctionName)) {
            Assert::true(
                self::propertyHookExists($originClassName, $originFunctionName),
                sprintf('Expected the property hook "%s" to exist on class "%s".', $originFunctionName, $originClassName),
            );
        } else {
            Assert::methodExists($originClassName, $originFunctionName);
        }
    }

    private static function referencesPropertyHook(string $functionName): bool
    {
        return str_starts_with($functionName, '$');
    }

    /** @param class-string $className */
    private static function propertyHookExists(string $className, string $functionName): bool
    {
        // Property hooks are only available as of PHP 8.4
        if (\PHP_VERSION_ID < 80400) {
            return false;
        }

        $hookReference = explode('::', substr($functionName, 1), 2);

        if (2 !== count($hookReference)) {
            return false;
        }

        [$propertyName, $hookName] = $hookReference;

        if (!property_exists($className, $propertyName)) {
            return false;
        }

        $property = new ReflectionProperty($className, $propertyName);

        /** @psalm-suppress UndefinedMethod */
        /** @var array<non-empty-string,ReflectionMethod> $hooks */
        $hooks = $property->getHooks(); // @phpstan-ignore method.notFound (getHooks() is available as of PHP 8.4)

        return isset($hooks[$hookName]);
    }
}
