<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Sets;

use Closure;
use PhPhD\ExceptionalValidation\Model\CaptureRule;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownException;

final class LazyRuleSet implements CaptureRule
{
    /** @param Closure(): CaptureRule $ruleSetFactory */
    public function __construct(
        private readonly Closure $ruleSetFactory,
    ) {
    }

    public function capture(ThrownException $thrownException): array
    {
        return ($this->ruleSetFactory)()->capture($thrownException);
    }

    public function getPropertyPath(): PropertyPath
    {
        return ($this->ruleSetFactory)()->getPropertyPath();
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
