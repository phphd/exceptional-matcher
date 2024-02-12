<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model;

use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownException;

/** @internal */
final class PropertyRuleSet implements CaptureRule
{
    public function __construct(
        private readonly CaptureRule $parent,
        private readonly string $name,
        private readonly mixed $value,
        private readonly CaptureRule $ruleSet,
    ) {
    }

    public function capture(ThrownException $thrownException): array
    {
        return $this->ruleSet->capture($thrownException);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath()->with($this->name);
    }

    public function getRoot(): object
    {
        return $this->parent->getRoot();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
