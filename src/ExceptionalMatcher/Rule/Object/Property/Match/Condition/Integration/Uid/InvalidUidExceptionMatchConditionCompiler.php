<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Integration\Uid;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Stringable;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Webmozart\Assert\Assert;

use function is_a;

/** @api */
const uid_value = InvalidUidExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<InvalidUidException>
 * @implements MatchConditionBlueprint<InvalidUidException>
 */
final class InvalidUidExceptionMatchConditionCompiler implements MatchConditionCompiler, MatchConditionBlueprint
{
    /** @return MatchConditionBlueprint<InvalidUidException> */
    public function compile(Catch_ $catch): MatchConditionBlueprint
    {
        if (!is_a($catch->getExceptionClass(), InvalidUidException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('InvalidUidExceptionMatchCondition can only be used for '.InvalidUidException::class);
        }

        return $this;
    }

    /** @return MatchCondition<InvalidUidException> */
    public function bind(MappingRule $rule): MatchCondition
    {
        $value = $rule->getValue();

        if (null === $value) {
            /** @psalm-var FalseCondition<InvalidUidException> */
            return new FalseCondition();
        }

        return new InvalidUidExceptionMatchCondition($this->string($value));
    }

    private function string(mixed $value): string
    {
        if ($value instanceof Stringable) {
            return (string)$value;
        }

        Assert::string($value, 'InvalidUidExceptionMatchCondition requires a stringable value, got: %s.');

        return $value;
    }
}
