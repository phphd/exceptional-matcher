<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\ento\Plan;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\ento\Compiler\MatchConditionPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ento\Formatter\MatchedExceptionFormatter;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 */
final class CatchExceptionMappingPlan
{
    public function __construct(
        /** @var MatchConditionPlan<TException> */
        private readonly MatchConditionPlan $conditionPlan,
        /** @var class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly string $formatterId,
        private readonly ?string $messageTemplate,
    ) {
    }

    /** @return CatchExceptionMappingNode<TException> */
    public function bind(ExceptionMappingNode $ownerRule): CatchExceptionMappingNode
    {
        return new CatchExceptionMappingNode(
            $ownerRule,
            $this->conditionPlan->bind($ownerRule),
            $this->formatterId,
            $this->messageTemplate,
        );
    }

    /** @return class-string<MatchedExceptionFormatter<TException,mixed>> */
    public function getFormatterId(): string
    {
        return $this->formatterId;
    }
}
