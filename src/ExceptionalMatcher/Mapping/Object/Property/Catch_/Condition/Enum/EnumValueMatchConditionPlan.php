<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum;

use BackedEnum;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Stringable;
use ValueError;
use Webmozart\Assert\Assert;

use function is_int;

/**
 * @internal
 *
 * @implements MatchConditionPlan<ValueError>
 */
final class EnumValueMatchConditionPlan implements MatchConditionPlan
{
    public function __construct(
        /** @var class-string<BackedEnum> */
        private readonly string $enumClassName,
    ) {
    }

    /** @return MatchCondition<ValueError> */
    public function bind(ExceptionMappingNode $rule): MatchCondition
    {
        $value = $rule->getValue();

        if (null === $value) {
            /** @psalm-var FalseCondition<ValueError> */
            return new FalseCondition();
        }

        return new EnumValueMatchCondition(
            $this->enumClassName,
            $this->intOrString($value),
        );
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
