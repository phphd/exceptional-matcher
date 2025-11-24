<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object;

use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final readonly class ObjectRuleSet implements CaptureRule
{
    public function __construct(
        private object $object,
        private ?CaptureRule $parent,
        private CaptureRule $ruleSet,
    ) {
    }

    public function process(ExceptionPackage $package): bool
    {
        return $this->ruleSet->process($package);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent?->getPropertyPath() ?? PropertyPath::empty();
    }

    public function getEnclosingObject(): object
    {
        return $this->object;
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
