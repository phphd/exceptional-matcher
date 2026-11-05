<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Class;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\PreCompiledMatchConditionBlueprint;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class ExceptionClassMatchConditionCompiler implements MatchConditionCompiler
{
    public const EXCEPTION_TYPE_GUARANTEE_MESSAGE = 'Exception matching can only be done for classes implementing Throwable';

    /** @return PreCompiledMatchConditionBlueprint<Throwable> */
    public function compile(ReflectionProperty $property, Catch_ $catch): PreCompiledMatchConditionBlueprint
    {
        Assert::isAOf($exceptionClass = $catch->getExceptionClass(), Throwable::class, self::EXCEPTION_TYPE_GUARANTEE_MESSAGE);

        /** @psalm-suppress ImplicitToStringCast */
        $condition = new ExceptionClassMatchCondition($exceptionClass);

        return new PreCompiledMatchConditionBlueprint($condition);
    }
}
