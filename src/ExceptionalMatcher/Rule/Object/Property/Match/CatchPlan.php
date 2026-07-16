<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use Throwable;

/**
 * @internal
 *
 * @template TException of Throwable
 */
final class CatchPlan
{
    public function __construct(
        /** @var MatchConditionBlueprint<TException> */
        private readonly MatchConditionBlueprint $conditionBlueprint,
        /** @var class-string<MatchedExceptionFormatter<TException,mixed>> */
        private readonly string $formatterId,
        private readonly ?string $messageTemplate,
    ) {
    }

    /** @return MatchExceptionRule<TException> */
    public function bind(MatchingRule $ownerRule): MatchExceptionRule
    {
        return new MatchExceptionRule(
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
