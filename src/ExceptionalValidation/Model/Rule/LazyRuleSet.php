<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use Closure;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownExceptions;

/** @internal */
final class LazyRuleSet implements CaptureRule
{
    /** @param Closure(): CaptureRule $ruleSetFactory */
    public function __construct(
        private readonly Closure $ruleSetFactory,
    ) {
    }

    public function capture(ThrownExceptions $thrownExceptions): array
    {
        return ($this->ruleSetFactory)()->capture($thrownExceptions);
    }

    public function getPropertyPath(): PropertyPath
    {
        return ($this->ruleSetFactory)()->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return ($this->ruleSetFactory)()->getEnclosingObject();
    }

    public function getRoot(): object
    {
        return ($this->ruleSetFactory)()->getRoot();
    }

    public function getValue(): mixed
    {
        return ($this->ruleSetFactory)()->getValue();
    }
}
