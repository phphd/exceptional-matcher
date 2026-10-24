<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;

use Webmozart\Assert\Assert;

use function is_a;

/** @api */
const exception_value = ExceptionValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValueException>
 * @implements MatchConditionPlan<ValueException>
 */
final class ExceptionValueMatchConditionCompiler implements MatchConditionCompiler, MatchConditionPlan
{
    public const TYPE_GUARANTEE_MESSAGE = 'ExceptionValueMatchCondition can only be used for exception classes that implement ValueException';

    /** @return MatchConditionPlan<ValueException> */
    public function compile(Catch_ $catch): MatchConditionPlan
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), ValueException::class, self::TYPE_GUARANTEE_MESSAGE);

        return $this;
    }

    public function bind(ExceptionMappingNode $node): ExceptionValueMatchCondition
    {
        return new ExceptionValueMatchCondition($node->getValue());
    }
}
