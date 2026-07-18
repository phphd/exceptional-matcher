<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use Closure;
use LogicException;
use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

use function is_object;

/** @internal */
final class ItemOfIterableMatchingRule implements MatchingRule
{
    private readonly MatchingRule $objectRuleSet;

    /** @param Closure(self): MatchingRule $ruleSet */
    public function __construct(
        private readonly int|string $key,
        private readonly MatchingRule $owner,
        Closure $ruleSet,
    ) {
        $this->objectRuleSet = $ruleSet($this);
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->objectRuleSet->process($reciprocal);
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
        $object = $this->objectRuleSet->getValue();

        if (!is_object($object)) {
            throw new LogicException('Object rule set must have returned an object as the value.');
        }

        return $object;
    }
}
