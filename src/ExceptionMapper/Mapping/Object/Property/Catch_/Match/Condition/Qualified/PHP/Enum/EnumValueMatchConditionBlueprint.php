<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum;

use BackedEnum;
use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\MatchCondition;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\IntType;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\StringType;
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
        /** @phpstan-var IntType */
        static $intType = new IntType();

        return $intType;
    }

    public static function isString(): StringType
    {
        static $stringType = new StringType();

        /** @phpstan-var StringType */
        return $stringType;
    }
}
