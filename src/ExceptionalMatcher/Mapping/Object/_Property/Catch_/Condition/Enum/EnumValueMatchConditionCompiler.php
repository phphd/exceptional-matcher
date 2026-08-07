<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Enum;

use BackedEnum;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\_Compiler\MatchConditionCompiler;
use ReflectionEnum;
use ValueError;

use function is_a;
use function sprintf;
use function var_export;

/** @api */
const enum_value = EnumValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValueError>
 */
final class EnumValueMatchConditionCompiler implements MatchConditionCompiler
{
    public function compile(Catch_ $catch): EnumValueMatchConditionPlan
    {
        if (!is_a($catch->getExceptionClass(), ValueError::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('EnumValueMatchCondition can only be used for '.ValueError::class);
        }

        @[$enumClassName, $fromMethod] = $catch->getFrom(); // @phpstan-ignore offsetAccess.nonArray

        self::assertEnumClass($enumClassName);
        self::assertEnumFromMethod($enumClassName, $fromMethod);

        return new EnumValueMatchConditionPlan($enumClassName);
    }

    /** @phpstan-assert class-string<BackedEnum> $className */
    private static function assertEnumClass(?string $className): void
    {
        if (null === $className || !is_a($className, BackedEnum::class, true)) {
            throw new LogicException(sprintf(
                'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s',
                var_export($className, true),
            ));
        }
    }

    /** @param class-string<BackedEnum> $enumClassName */
    private static function assertEnumFromMethod(string $enumClassName, ?string $fromMethod): void
    {
        if (null === $fromMethod || 'from' === $fromMethod) {
            return;
        }

        $enumReflection = new ReflectionEnum($enumClassName);

        throw new LogicException(sprintf(
            'EnumValueMatchCondition must specify `from: [%1$s::class, \'from\']`, got: `from: [%1$s::class, \'%2$s\']`.',
            $enumReflection->getShortName(),
            $fromMethod,
        ));
    }
}
