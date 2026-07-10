<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
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
 * @implements MatchCondition<Throwable>
 */
final class ExceptionOriginMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var ?class-string */
        private readonly ?string $originClassName = null,
        /** @var ?non-empty-string */
        private readonly ?string $originFunctionName = null,
    ) {
        if (null !== $this->originClassName && null !== $this->originFunctionName) {
            if (self::referencesPropertyHook($this->originFunctionName)) {
                Assert::true(
                    self::propertyHookExists($this->originClassName, $this->originFunctionName),
                    sprintf('Expected the property hook "%s" to exist on class "%s".', $this->originFunctionName, $this->originClassName),
                );
            } else {
                Assert::methodExists($this->originClassName, $this->originFunctionName);
            }
        } elseif (null !== $this->originClassName) {
            Assert::classExists($this->originClassName);
        } elseif (null !== $this->originFunctionName) {
            Assert::true(function_exists($this->originFunctionName));
        } else {
            throw new LogicException('At least one of the originClassName or originFunctionName must be set.');
        }
    }

    public function matches(Throwable $exception): bool
    {
        foreach ($exception->getTrace() as $traceItem) {
            if (isset($this->originClassName) && $this->originClassName !== ($traceItem['class'] ?? null)) {
                continue;
            }

            if (isset($this->originFunctionName) && $this->originFunctionName !== ($traceItem['function'] ?? null)) { // @phpstan-ignore nullCoalesce.offset
                continue;
            }

            return true;
        }

        return false;
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
