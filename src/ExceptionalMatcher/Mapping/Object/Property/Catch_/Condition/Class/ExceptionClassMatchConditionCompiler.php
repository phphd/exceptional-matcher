<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\PreCompiledMatchConditionPlan;
use Throwable;

use Webmozart\Assert\Assert;

use function is_a;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class ExceptionClassMatchConditionCompiler implements MatchConditionCompiler
{
    public const TYPE_GUARANTEE_MESSAGE = 'Exception matching can only be done for classes implementing Throwable';

    /** @return PreCompiledMatchConditionPlan<Throwable> */
    public function compile(Catch_ $catch): PreCompiledMatchConditionPlan
    {
        Assert::isAOf($exceptionClass = $catch->getExceptionClass(), Throwable::class, self::TYPE_GUARANTEE_MESSAGE);

        /** @psalm-suppress ImplicitToStringCast */
        $condition = new ExceptionClassMatchCondition($exceptionClass);

        return new PreCompiledMatchConditionPlan($condition);
    }
}
