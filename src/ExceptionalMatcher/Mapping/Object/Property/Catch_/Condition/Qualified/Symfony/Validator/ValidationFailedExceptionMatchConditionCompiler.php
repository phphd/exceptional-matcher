<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Validator;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Webmozart\Assert\Assert;

/** @api */
const validated_value = ValidationFailedExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<ValidationFailedException>
 * @implements MatchConditionPlan<ValidationFailedException>
 */
final class ValidationFailedExceptionMatchConditionCompiler implements MatchConditionCompiler, MatchConditionPlan
{
    public const TYPE_GUARANTEE_MESSAGE = 'ValidationFailedExceptionMatchCondition can only be used for ValidationFailedException';

    /** @return MatchConditionPlan<ValidationFailedException> */
    public function compile(Catch_ $catch): MatchConditionPlan
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), ValidationFailedException::class, self::TYPE_GUARANTEE_MESSAGE);

        return $this;
    }

    public function bind(ExceptionMappingNode $node): ValidationFailedExceptionMatchCondition
    {
        return new ValidationFailedExceptionMatchCondition($node->getValue());
    }
}
