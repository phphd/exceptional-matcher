<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

use function is_object;

/** @internal */
final class ItemOfIterableMatchingRule implements MatchingRule
{
    public function __construct(
        private readonly int|string $key,
        private readonly MatchingRule $parent,
        private readonly MatchingRule $objectRuleSet,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->objectRuleSet->process($reciprocal);
    }

    public function getParent(): MatchingRule
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
