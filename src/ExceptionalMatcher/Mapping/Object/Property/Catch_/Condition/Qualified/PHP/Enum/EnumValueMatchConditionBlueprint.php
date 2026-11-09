<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum;

use BackedEnum;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\IntType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\StringType;
use ValueError;

/**
 * @internal
 *
 * @implements MatchConditionBlueprint<ValueError>
 */
final class EnumValueMatchConditionBlueprint implements MatchConditionBlueprint
{
    public function __construct(
        /** @var class-string<BackedEnum> */
        private readonly string $enumClassName,
    ) {
    }

    /** @return MatchCondition<ValueError> */
    public function bind(ExceptionMappingNode $node): MatchCondition
    {
        $value = $node->getValue();

        if (!self::isInt()($value) && !self::isString()($value)) {
            /** @psalm-var FalseCondition<ValueError> */
            return new FalseCondition();
        }

        return new EnumValueMatchCondition($this->enumClassName, $value);
    }

    public static function isInt(): IntType
    {
        /** @phpstan-var IntType $intType */
        static $intType = new IntType();

        return $intType;
    }

    public static function isString(): StringType
    {
        /** @phpstan-var StringType $stringType */
        static $stringType = new StringType();

        return $stringType;
    }
}
