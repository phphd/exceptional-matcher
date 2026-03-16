<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Uid;

use LogicException;
use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Stringable;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Webmozart\Assert\Assert;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionFactory<InvalidUidException>
 */
final class InvalidUidExceptionMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $parent): MatchCondition
    {
        if (!is_a($catch->getExceptionClass(), InvalidUidException::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('InvalidUidExceptionMatchCondition can only be used for '.InvalidUidException::class);
        }

        $value = $parent->getValue();

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
