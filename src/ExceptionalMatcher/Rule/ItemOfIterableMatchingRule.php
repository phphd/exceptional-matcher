<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use LogicException;
use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

use function is_object;

/** @internal */
final class ItemOfIterableMatchingRule implements MatchingRule
{
    private ?MatchingRule $objectRuleSet = null;

    private function __construct(
        private readonly int|string $key,
        private readonly MatchingRule $owner,
    ) {
    }

    /**
     * The item rule and its object rule set reference each other: the object rule set is created
     * against the item rule as its owner, hence it is assigned right after construction.
     */
    public static function forPlan(int|string $key, MatchingRule $owner, ClassMatchingPlan $itemPlan, object $item): self
    {
        $itemRule = new self($key, $owner);

        $itemRule->objectRuleSet = $itemPlan->bind($item, $itemRule);

        return $itemRule;
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->getObjectRuleSet()
            ->process($reciprocal)
        ;
    }

    public function getOwner(): MatchingRule
    {
        return $this->owner;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->owner->getPropertyPath()
            ->at($this->key)
        ;
    }

    public function getEnclosingObject(): object
    {
        return $this->owner->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->owner->getRootObject();
    }

    public function getValue(): object
    {
        $object = $this->getObjectRuleSet()
            ->getValue()
        ;

        if (!is_object($object)) {
            throw new LogicException('Object rule set must have returned an object as the value.');
        }

        return $object;
    }

    private function getObjectRuleSet(): MatchingRule
    {
        if (null === $this->objectRuleSet) {
            throw new LogicException('Item of iterable matching rule must have been given an object rule set.');
        }

        return $this->objectRuleSet;
    }
}
