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
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use Symfony\Component\Validator\Constraints\Valid;
use Webmozart\Assert\Assert;

use function is_iterable;
use function is_object;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyRulesAssembler>
 */
final readonly class PropertyNestedValidIterableRulesAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        private ObjectRuleSetAssemblerService $objectRuleSetAssembler,
    ) {
    }

    /** @param PropertyRulesAssembler $assembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        Assert::isInstanceOf($parentRule, PropertyRuleSet::class);

        $propertyValue = $parentRule->getValue();

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

        return $this->createRuleSet($propertyValue, $parentRule);
    }

    private function isMarkedWithValidAttribute(PropertyRulesAssembler $envelope): bool
    {
        $validAttributes = $envelope->getReflectionProperty()->getAttributes(Valid::class);

        return [] !== $validAttributes;
    }

    /** @param iterable<array-key,mixed> $items */
    private function createRuleSet(iterable $items, CaptureRule $parent): CompositeRuleSet
    {
        return new CompositeRuleSet(
            $parent,
            $this->getRules($items, $parent),
        );
    }

    /** @param iterable<array-key,mixed> $items */
    private function getRules(iterable $items, CaptureRule $parentRuleSet): Generator
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

    private function getIterableItemCaptureRule(CaptureRule $parentRuleSet, int|string $key, object $object): ?CaptureRule
    {
        return (new LazyRuleSet(
            /** @param LazyRuleSet<ItemOfIterableCaptureRule> $itemOfIterableRule */
            function (LazyRuleSet $itemOfIterableRule) use ($key, $parentRuleSet, $object): ?ItemOfIterableCaptureRule {
                $objectRuleSet = $this->objectRuleSetAssembler->assembleForMessage($object, $itemOfIterableRule);

                if (null === $objectRuleSet) {
                    return null;
                }

                return new ItemOfIterableCaptureRule($key, $parentRuleSet, $objectRuleSet);
            },
        ))->build();
    }
}
