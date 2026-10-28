<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 */
final class CatchExceptionMappingBlueprint implements ExceptionMappingBlueprint
{
    public function __construct(
        /** @var MatchConditionPlan<TException> */
        private readonly MatchConditionPlan $conditionPlan,
        /** @var ?class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly ?string $formatterId,
        private readonly ?string $messageTemplate,
    ) {
    }

    /** @return CatchExceptionMappingNode<TException> */
    public function bind(ExceptionMappingNode $property): CatchExceptionMappingNode
    {
        return new CatchExceptionMappingNode(
            $property,
            $this->conditionPlan->bind($property),
            $this->formatterId,
            $this->messageTemplate,
        );
    }

    /** @return MatchConditionPlan<TException> */
    public function getConditionPlan(): MatchConditionPlan
    {
        return $this->conditionPlan;
    }
}
