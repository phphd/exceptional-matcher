<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum;

use BackedEnum;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use ReflectionEnum;
use ValueError;
use Webmozart\Assert\Assert;

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
    public const TYPE_GUARANTEE_MESSAGE = 'EnumValueMatchCondition can only be used for '.ValueError::class;

    public function compile(Catch_ $catch): EnumValueMatchConditionBlueprint
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), ValueError::class, self::TYPE_GUARANTEE_MESSAGE);

        @[$enumClassName, $fromMethod] = $catch->getFrom(); // @phpstan-ignore offsetAccess.nonArray

        self::assertEnumClass($enumClassName);
        self::assertEnumFromMethod($enumClassName, $fromMethod);

        return new EnumValueMatchConditionBlueprint($enumClassName);
    }

    /** @phpstan-assert class-string<BackedEnum> $className */
    private static function assertEnumClass(?string $className): void
    {
        if (null === $className || !is_a($className, BackedEnum::class, allow_string: true)) {
            throw new LogicException(sprintf(
                'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s',
                var_export($className, return: true),
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
