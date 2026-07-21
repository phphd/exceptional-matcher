<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum;

use BackedEnum;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Stringable;
use ValueError;
use Webmozart\Assert\Assert;

use function is_int;

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
    public function bind(MappingRule $rule): MatchCondition
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
