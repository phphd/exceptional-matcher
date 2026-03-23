<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules;

use Generator;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\ItemOfIterableMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\LazyMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet;

use function is_iterable;
use function is_object;

/**
 * @internal
 *
 * @implements MatchingRuleSetAssemblerService<PropertyMatchingRulesAssembler>
 */
final class PropertyNestedValidIterableRulesAssemblerService implements MatchingRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var MatchingRuleSetAssemblerService<ObjectMatchingRuleSetAssembler> */
        private readonly MatchingRuleSetAssemblerService $objectRuleSetAssemblerService,
    ) {
    }

    /** @param PropertyMatchingRulesAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?MatchingRule
    {
        $propertyRuleSet = $assembler->getOwnerRule();
        $propertyValue = $propertyRuleSet->getValue();

        if (!is_iterable($propertyValue)) {
            return null;
        }

        if ([] === $propertyValue) {
            return null;
        }

        /** @var iterable<array-key,mixed> $propertyValue */

        return $this->createRuleSet($propertyValue, $propertyRuleSet);
    }

    /** @param iterable<array-key,mixed> $items */
    private function createRuleSet(iterable $items, PropertyMatchingRuleSet $ownerRuleSet): CompositeMatchingRule
    {
        return new CompositeMatchingRule(
            $ownerRuleSet,
            $this->getRules($items, $ownerRuleSet),
        );
    }

    /** @param iterable<array-key,mixed> $items */
    private function getRules(iterable $items, PropertyMatchingRuleSet $ownerRuleSet): Generator
    {
        foreach ($items as $key => $item) {
            if (!is_object($item)) {
                continue;
            }

            $rule = $this->getIterableItemMatchingRule($ownerRuleSet, $key, $item);

            if (null !== $rule) {
                yield $rule;
            }
        }
    }

    private function getIterableItemMatchingRule(PropertyMatchingRuleSet $ownerRuleSet, int|string $key, object $object): ?MatchingRule
    {
        $wrappedObjectRuleSet = new LazyMatchingRule(
            /** @param LazyMatchingRule<MatchingRule> $lazyObjectRuleSet */
            function (LazyMatchingRule $lazyObjectRuleSet) use ($key, $ownerRuleSet, $object): ?MatchingRule {
                $itemOfIterableRule = new ItemOfIterableMatchingRule($key, $ownerRuleSet, $lazyObjectRuleSet);

                return $this->objectRuleSetAssemblerService
                    ->assemble(new ObjectMatchingRuleSetAssembler($object, $itemOfIterableRule))
                ;
            },
        );

        return $wrappedObjectRuleSet->build()?->getOwner();
    }
}
