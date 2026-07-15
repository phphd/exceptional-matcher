<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum;

use BackedEnum;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use ReflectionEnum;
use ReflectionNamedType;
use Throwable;
use ValueError;

use function sprintf;

/**
 * @internal - use {@see enum_value} constant for a class reference instead
 *
 * @implements MatchCondition<ValueError>
 */
final class EnumValueMatchCondition implements MatchCondition
{
    private readonly string $expectedMessage;

    /** @param class-string<BackedEnum> $enumClassName */
    public function __construct(string $enumClassName, int|string $propertyValue)
    {
        $enumReflection = new ReflectionEnum($enumClassName);

        /** @var ReflectionNamedType $enumType */
        $enumType = $enumReflection->getBackingType();

        if ('int' === $enumType->getName()) {
            $value = (int)$propertyValue;
        } else {
            $value = self::quote($propertyValue);
        }

        $this->expectedMessage = $value.' is not a valid backing value for enum '.self::enumReference($enumClassName);
    }

    /** @param ValueError $exception */
    public function matches(Throwable $exception): bool
    {
        return $exception->getMessage() === $this->expectedMessage;
    }

    private static function enumReference(string $enumClassName): string
    {
        if (\PHP_VERSION_ID < 80200) {
            return self::quote($enumClassName);
        }

        return $enumClassName;
    }

    private static function quote(int|string $value): string
    {
        return sprintf('"%s"', $value);
    }
}
