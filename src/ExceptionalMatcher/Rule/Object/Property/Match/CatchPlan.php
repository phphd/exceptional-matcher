<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionPlan;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 */
final class CatchPlan
{
    public function __construct(
        /** @var MatchConditionPlan<TException> */
        private readonly MatchConditionPlan $conditionBlueprint,
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
            $this->conditionBlueprint->bind($ownerRule),
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
