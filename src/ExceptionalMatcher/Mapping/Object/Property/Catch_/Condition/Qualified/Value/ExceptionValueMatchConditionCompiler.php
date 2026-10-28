<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Value;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use Webmozart\Assert\Assert;

/** @api */
const exception_value = ExceptionValueMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValueException>
 * @implements MatchConditionBlueprint<ValueException>
 */
final class ExceptionValueMatchConditionCompiler implements MatchConditionCompiler, MatchConditionBlueprint
{
    public const TYPE_GUARANTEE_MESSAGE = 'ExceptionValueMatchCondition can only be used for exception classes that implement ValueException';

    /** @return MatchConditionBlueprint<ValueException> */
    public function compile(Catch_ $catch): MatchConditionBlueprint
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
