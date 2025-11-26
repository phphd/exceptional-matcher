<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property;

use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final readonly class PropertyRuleSet implements CaptureRule
{
    public function __construct(
        private CaptureRule $parent,
        private string $name,
        private mixed $value,
        private CaptureRule $ruleSet,
    ) {
    }

    public function process(ExceptionPackage $package): bool
    {
        return $this->ruleSet->process($package);
    }

    public function getParent(): CaptureRule
    {
        return $this->parent;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath()->with($this->name);
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->parent->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
