<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum;

use BackedEnum;
use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use ReflectionEnum;
use Stringable;
use ValueError;
use Webmozart\Assert\Assert;

use function is_a;
use function is_int;
use function sprintf;
use function var_export;

/** @api */
const enum_value = EnumValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionFactory<ValueError>
 */
final class EnumValueMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $owner): MatchCondition
    {
        if (!is_a($catch->getExceptionClass(), ValueError::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('EnumValueMatchCondition can only be used for '.ValueError::class);
        }

        $value = $owner->getValue();

        if (null === $value) {
            /** @psalm-var FalseCondition<ValueError> */
            return new FalseCondition();
        }

        return new EnumValueMatchCondition(
            $this->getEnumClassName($catch),
            $this->intOrString($value),
        );
    }

    /**
     * @param Catch_<ValueError,ValueError> $catch
     *
     * @return class-string<BackedEnum>
     */
    private function getEnumClassName(Catch_ $catch): string
    {
        @[$className, $fromMethod] = $catch->getFrom(); // @phpstan-ignore offsetAccess.nonArray

        $this->assertIsEnumClass($className);
        $this->assertIsEnumFromMethod($className, $fromMethod);

        return $className;
    }

    /**
     * @param ?class-string $className
     *
     * @phpstan-assert class-string<BackedEnum> $className
     */
    private function assertIsEnumClass(?string $className): void
    {
        if (null === $className || !is_a($className, BackedEnum::class, true)) {
            throw new LogicException(sprintf(
                'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s',
                var_export($className, true),
            ));
        }
    }

    /** @param class-string<BackedEnum> $enumClassName */
    private function assertIsEnumFromMethod(string $enumClassName, ?string $fromMethod): void
    {
        if (null !== $fromMethod && 'from' !== $fromMethod) {
            $enumReflection = new ReflectionEnum($enumClassName);

            throw new LogicException(sprintf(
                'EnumValueMatchCondition must specify `from: [%1$s::class, \'from\']`, got: `from: [%1$s::class, \'%2$s\']`.',
                $enumReflection->getShortName(),
                $fromMethod,
            ));
        }
    }

    private function intOrString(mixed $value): int|string
    {
        if (is_int($value)) {
            return $value;
        }

        if ($value instanceof Stringable) {
            return (string)$value;
        }

        Assert::string($value, 'EnumValueMatchCondition requires an int|string value, got: %s.');

        return $value;
    }
}
