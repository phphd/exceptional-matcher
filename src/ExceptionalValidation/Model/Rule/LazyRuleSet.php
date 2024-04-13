<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use Closure;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownExceptions;

/** @internal */
final class LazyRuleSet implements CaptureRule
{
    private ?CaptureRule $innerRule = null;

    /** @param Closure(LazyRuleSet): CaptureRule $ruleSetFactory */
    public function __construct(
        private readonly Closure $ruleSetFactory,
    ) {
    }

    public function capture(ThrownExceptions $thrownExceptions): array
    {
        return $this->innerRule()->capture($thrownExceptions);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->innerRule()->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->innerRule()->getEnclosingObject();
    }

    public function getRoot(): object
    {
        return $this->innerRule()->getRoot();
    }

    public function getValue(): mixed
    {
        return $this->innerRule()->getValue();
    }

    private function innerRule(): CaptureRule
    {
        return $this->innerRule ??= ($this->ruleSetFactory)($this);
    }
}
