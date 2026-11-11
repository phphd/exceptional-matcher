<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\StringType;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

/**
 * @internal
 *
 * @implements MatchConditionBlueprint<InvalidUidException>
 */
final class UidValueMatchConditionBlueprint implements MatchConditionBlueprint
{
    /** @return MatchCondition<InvalidUidException> */
    public function bind(ExceptionMappingNode $node): MatchCondition
    {
        $value = $node->getValue();

        if (!self::isString()($value)) {
            /** @psalm-var FalseCondition<InvalidUidException> */
            return new FalseCondition();
        }

        return new UidValueMatchCondition($value);
    }

    public static function isString(): StringType
    {
        static $isString = new StringType();

        /** @phpstan-var StringType */
        return $isString;
    }
}
