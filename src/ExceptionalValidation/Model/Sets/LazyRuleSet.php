<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Sets;

use Closure;
use PhPhD\ExceptionalValidation\Model\CaptureRule;
use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use Throwable;

final class LazyRuleSet implements CaptureRule
{
    /** @param Closure(): CaptureRule $ruleSetFactory */
    public function __construct(
        private readonly Closure $ruleSetFactory,
    ) {
    }

    public function capture(Throwable $exception): ?CaughtException
    {
        return ($this->ruleSetFactory)()->capture($exception);
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
