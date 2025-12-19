<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use Generator;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use ReflectionClass;

/** @internal */
final class ObjectRuleSetAssembler implements CaptureRuleSetAssembler
{
    /** @var ReflectionClass<object> */
    private readonly ReflectionClass $reflectionClass;

    public function __construct(
        private readonly object $message,
        private readonly ?CaptureRule $parentRule = null,
    ) {
        $this->reflectionClass = new ReflectionClass($this->message::class);
    }

    public function assemble(ObjectRuleSetAssemblerService $service): ?CaptureRule
    {
        if (!$this->isMarkedWithAnAttribute()) {
            return null;
        }

        $wrappedRuleSet = new LazyRuleSet(
            /** @param LazyRuleSet<CompositeRuleSet> $lazyWrappedRuleSet */
            function (LazyRuleSet $lazyWrappedRuleSet) use ($service): CompositeRuleSet {
                $objectRuleSet = new ObjectRuleSet(
                    $this->message,
                    $this->parentRule,
                    $lazyWrappedRuleSet,
                );

                return new CompositeRuleSet(
                    $objectRuleSet,
                    $this->getPropertyRules($objectRuleSet, $service),
                );
            },
        );

        return $wrappedRuleSet->build()?->getParent();
    }

    public function getParentRule(): ?CaptureRule
    {
        return $this->parentRule;
    }

    private function isMarkedWithAnAttribute(): bool
    {
        return [] !== $this->reflectionClass->getAttributes(ExceptionalValidation::class);
    }

    private function getPropertyRules(ObjectRuleSet $objectRuleSet, ObjectRuleSetAssemblerService $service): Generator
    {
        foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
            $propertyRuleSet = $service->propertyRuleSetAssemblerService
                ->assemble(new PropertyRuleSetAssembler($objectRuleSet, $reflectionProperty))
            ;

            if (null !== $propertyRuleSet) {
                yield $propertyRuleSet;
            }
        }
    }
}
