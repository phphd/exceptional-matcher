<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value;

use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionCompiler;
use ReflectionProperty;
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
    public const EXCEPTION_TYPE_GUARANTEE_MESSAGE = 'ExceptionValueMatchCondition can only be used for exception classes that implement ValueException';

    /** @return MatchConditionBlueprint<ValueException> */
    public function compile(ReflectionProperty $property, Catch_ $catch): MatchConditionBlueprint
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), ValueException::class, self::EXCEPTION_TYPE_GUARANTEE_MESSAGE);

        return $this;
    }

    public function bind(ExceptionMappingNode $node): ExceptionValueMatchCondition
    {
        return new ExceptionValueMatchCondition($node->getValue());
    }
}
