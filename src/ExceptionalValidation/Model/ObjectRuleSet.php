<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model;

use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use Throwable;

/** @internal */
final class ObjectRuleSet implements CaptureRule
{
    public function __construct(
        private readonly object $object,
        private readonly ?CaptureRule $parent,
        private readonly CaptureRule $ruleSet,
    ) {
    }

    public function capture(Throwable $exception): ?CaughtException
    {
        return $this->ruleSet->capture($exception);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent?->getPropertyPath() ?? PropertyPath::empty();
    }

    public function getRoot(): object
    {
        return $this->parent?->getRoot() ?? $this->object;
    }

    public function getValue(): object
    {
        return $this->object;
    }
}
