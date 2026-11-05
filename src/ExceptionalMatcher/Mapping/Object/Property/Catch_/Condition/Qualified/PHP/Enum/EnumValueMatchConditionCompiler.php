<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum;

use BackedEnum;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\PropertyType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\AnyType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\IntType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\StringType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\TypeSpecification;
use ReflectionEnum;
use ReflectionProperty;
use ReflectionType;
use ValueError;
use Webmozart\Assert\Assert;

use function is_a;
use function PHPUnit\Framework\returnArgument;
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
    public const EXCEPTION_TYPE_GUARANTEE_MESSAGE = 'EnumValueMatchCondition can only be used for '.ValueError::class;
    public const PROPERTY_TYPE_GUARANTEE_MESSAGE = 'EnumValueMatchCondition requires an int|string property, got: %s';

    public function compile(ReflectionProperty $property, Catch_ $catch): EnumValueMatchConditionBlueprint
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), ValueError::class, self::EXCEPTION_TYPE_GUARANTEE_MESSAGE);

        $this->propertyType($property)->assertIncludes(self::intOrStringOrMixed(), self::PROPERTY_TYPE_GUARANTEE_MESSAGE);

        @[$enumClassName, $fromMethod] = $catch->getFrom(); // @phpstan-ignore offsetAccess.nonArray

        self::assertEnumClass($enumClassName);
        self::assertEnumFromMethod($enumClassName, $fromMethod);

        return new EnumValueMatchConditionBlueprint($enumClassName);
    }

    private function propertyType(ReflectionProperty $property): PropertyType
    {
        return new PropertyType($property->getType());
    }

    private static function intOrStringOrMixed(): AnyType
    {
        /** @phpstan-var ?AnyType $intOrStringOrMixed */
        static $intOrStringOrMixed;
        return $intOrStringOrMixed ??= new AnyType([
            EnumValueMatchConditionBlueprint::isInt(),
            EnumValueMatchConditionBlueprint::isString(),
            new MixedType(),
        ]);
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
