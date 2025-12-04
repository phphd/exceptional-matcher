<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule;

use LogicException;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;

use function is_object;

/** @internal */
final readonly class ItemOfIterableCaptureRule implements CaptureRule
{
    public function __construct(
        private int|string $key,
        private CaptureRule $parent,
        private CaptureRule $objectRuleSet,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->objectRuleSet->process($reciprocal);
    }

    public function getParent(): CaptureRule
    {
        return $this->parent;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath()->at($this->key);
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->parent->getRootObject();
    }

    public function getValue(): object
    {
        $object = $this->objectRuleSet->getValue();

        if (!is_object($object)) {
            throw new LogicException('Object rule set must have returned an object as the value.');
        }

        return $object;
    }
}
