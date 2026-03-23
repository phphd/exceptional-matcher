<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Stringable;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Webmozart\Assert\Assert;

use function is_a;

/** @api */
const uid_value = InvalidUidExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionFactory<InvalidUidException>
 */
final class InvalidUidExceptionMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $owner): MatchCondition
    {
        if (!is_a($catch->getExceptionClass(), InvalidUidException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('InvalidUidExceptionMatchCondition can only be used for '.InvalidUidException::class);
        }

        $value = $owner->getValue();

        if (null === $value) {
            /** @psalm-var FalseCondition<InvalidUidException> */
            return new FalseCondition();
        }

        if ($value instanceof Stringable) {
            $value = (string)$value;
        } else {
            Assert::string($value, 'InvalidUidExceptionMatchCondition requires a stringable value, got: %s.');
        }

        return new InvalidUidExceptionMatchCondition($value);
    }
}
