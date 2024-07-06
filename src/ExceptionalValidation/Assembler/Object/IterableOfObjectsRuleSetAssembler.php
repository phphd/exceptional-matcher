<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object;

use Generator;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Model\Rule\IterableItemCaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\LazyRuleSet;

use function array_filter;
use function is_array;
use function iterator_to_array;

/** @internal */
final class IterableOfObjectsRuleSetAssembler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $objectRuleSetAssembler,
    ) {
    }

    /** @param iterable<array-key,mixed> $items */
    public function assemble(iterable $items, CaptureRule $parent): ?CaptureRule
    {
        /** @var array<array-key,object> $objects */
        $objects = array_filter($this->convertToArray($items), is_object(...));

        if ([] === $objects) {
            return null;
        }

        return new LazyRuleSet(
            function (LazyRuleSet $lazyRuleSet) use ($parent, $objects): CompositeRuleSet {
                $rulesGenerator = $this->getRules($objects, $lazyRuleSet);

                return new CompositeRuleSet($parent, $rulesGenerator);
            },
        );
    }

    /** @param non-empty-array<array-key,object> $objects */
    private function getRules(array $objects, CaptureRule $iterableRuleSet): Generator
    {
        foreach ($objects as $key => $item) {
            $rule = $this->getIterableItemCaptureRule($iterableRuleSet, $key, $item);

            if (null !== $rule) {
                yield $rule;
            }
        }
    }

    private function getIterableItemCaptureRule(CaptureRule $parentIterableRuleSet, int|string $key, object $object): ?IterableItemCaptureRule
    {
        /** @var CaptureRule $rules */
        $rules = null;
        $rulesSet = new LazyRuleSet(static function () use (&$rules): CaptureRule {
            return $rules;
        });

        $arrayItemRule = new IterableItemCaptureRule($key, $parentIterableRuleSet, $rulesSet);

        $rules = $this->objectRuleSetAssembler->assemble($object, $arrayItemRule);

        if (null === $rules) {
            return null;
        }

        return $arrayItemRule;
    }

    /**
     * @param iterable<array-key,mixed> $iterable
     *
     * @return array<array-key,mixed>
     */
    private function convertToArray(iterable $iterable): array
    {
        if (is_array($iterable)) {
            return $iterable;
        }

        return iterator_to_array($iterable);
    }
}
