<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Stringable;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Webmozart\Assert\Assert;

/** @api */
const uid_value = InvalidUidExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<InvalidUidException>
 * @implements MatchConditionPlan<InvalidUidException>
 */
final class InvalidUidExceptionMatchConditionCompiler implements MatchConditionCompiler, MatchConditionPlan
{
    public const TYPE_GUARANTEE_MESSAGE = 'InvalidUidExceptionMatchCondition can only be used for '.InvalidUidException::class;

    /** @return MatchConditionPlan<InvalidUidException> */
    public function compile(Catch_ $catch): MatchConditionPlan
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), InvalidUidException::class, self::TYPE_GUARANTEE_MESSAGE);

        return $this;
    }

    /** @return MatchCondition<InvalidUidException> */
    public function bind(ExceptionMappingNode $node): MatchCondition
    {
        $value = $node->getValue();

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
