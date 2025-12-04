<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use Generator;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\ItemOfIterableCaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use Symfony\Component\Validator\Constraints\Valid;

use function is_iterable;
use function is_object;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyCaptureRulesAssembler>
 */
final readonly class PropertyNestedValidIterableRulesAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var CaptureRuleSetAssemblerService<ObjectRuleSetAssembler> */
        private CaptureRuleSetAssemblerService $objectRuleSetAssemblerService,
    ) {
    }

    /** @param PropertyCaptureRulesAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        $propertyValue = $assembler->getParentRule()->getValue();

        if (!is_iterable($propertyValue)) {
            return null;
        }

        if ([] === $propertyValue) {
            return null;
        }

        if (!$this->isMarkedWithValidAttribute($assembler)) {
            return null;
        }

        /** @var iterable<array-key,mixed> $propertyValue */

        return $this->createRuleSet($propertyValue, $assembler->getParentRule());
    }

    private function isMarkedWithValidAttribute(PropertyCaptureRulesAssembler $assembler): bool
    {
        $validAttributes = $assembler->getReflectionProperty()->getAttributes(Valid::class);

        return [] !== $validAttributes;
    }

    /** @param iterable<array-key,mixed> $items */
    private function createRuleSet(iterable $items, PropertyRuleSet $parent): CompositeRuleSet
    {
        return new CompositeRuleSet(
            $parent,
            $this->getRules($items, $parent),
        );
    }

    /** @param iterable<array-key,mixed> $items */
    private function getRules(iterable $items, PropertyRuleSet $parentRuleSet): Generator
    {
        foreach ($items as $key => $item) {
            if (!is_object($item)) {
                continue;
            }

            $rule = $this->getIterableItemCaptureRule($parentRuleSet, $key, $item);

            if (null !== $rule) {
                yield $rule;
            }
        }
    }

    private function getIterableItemCaptureRule(PropertyRuleSet $parentRuleSet, int|string $key, object $object): ?CaptureRule
    {
        $wrappedObjectRuleSet = new LazyRuleSet(
            /** @param LazyRuleSet<CaptureRule> $lazyObjectRuleSet */
            function (LazyRuleSet $lazyObjectRuleSet) use ($key, $parentRuleSet, $object): ?CaptureRule {
                $itemOfIterableRule = new ItemOfIterableCaptureRule($key, $parentRuleSet, $lazyObjectRuleSet);

                return $this->objectRuleSetAssemblerService
                    ->assemble(new ObjectRuleSetAssembler($object, $itemOfIterableRule))
                ;
            },
        );

        return $wrappedObjectRuleSet->build()?->getParent();
    }
}
