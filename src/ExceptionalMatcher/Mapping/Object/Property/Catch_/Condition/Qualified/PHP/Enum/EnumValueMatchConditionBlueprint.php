<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\PHP\Enum;

use BackedEnum;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use ValueError;

use function is_int;
use function is_string;

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

        if (!is_int($value) && !is_string($value)) {
            /** @psalm-var FalseCondition<ValueError> */
            return new FalseCondition();
        }

        return new EnumValueMatchCondition($this->enumClassName, $value);
    }
}
