<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule;

use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final readonly class CompositeRuleSet implements CaptureRule
{
    public function __construct(
        private CaptureRule $parent,
        /** @var iterable<CaptureRule> $rules */
        private iterable $rules,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->process($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getParent(): CaptureRule
    {
        return $this->parent;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
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
        return $this->parent->getValue();
    }
}
