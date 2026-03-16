<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use Generator;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalValidation\Rule\ItemOfIterableMatchingRule;
use PhPhD\ExceptionalValidation\Rule\LazyMatchingRule;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyMatchingRuleSet;

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
        $propertyRuleSet = $assembler->getParentRule();
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
    private function createRuleSet(iterable $items, PropertyMatchingRuleSet $parent): CompositeMatchingRule
    {
        return new CompositeMatchingRule(
            $parent,
            $this->getRules($items, $parent),
        );
    }

    /** @param iterable<array-key,mixed> $items */
    private function getRules(iterable $items, PropertyMatchingRuleSet $parentRuleSet): Generator
    {
        foreach ($items as $key => $item) {
            if (!is_object($item)) {
                continue;
            }

            $rule = $this->getIterableItemMatchingRule($parentRuleSet, $key, $item);

            if (null !== $rule) {
                yield $rule;
            }
        }
    }

    private function getIterableItemMatchingRule(PropertyMatchingRuleSet $parentRuleSet, int|string $key, object $object): ?MatchingRule
    {
        $wrappedObjectRuleSet = new LazyMatchingRule(
            /** @param LazyMatchingRule<MatchingRule> $lazyObjectRuleSet */
            function (LazyMatchingRule $lazyObjectRuleSet) use ($key, $parentRuleSet, $object): ?MatchingRule {
                $itemOfIterableRule = new ItemOfIterableMatchingRule($key, $parentRuleSet, $lazyObjectRuleSet);

                return $this->objectRuleSetAssemblerService
                    ->assemble(new ObjectMatchingRuleSetAssembler($object, $itemOfIterableRule))
                ;
            },
        );

        return $wrappedObjectRuleSet->build()?->getParent();
    }
}
