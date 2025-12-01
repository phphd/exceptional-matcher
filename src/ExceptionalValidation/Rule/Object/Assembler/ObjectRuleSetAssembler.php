<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use Generator;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use ReflectionClass;

/** @internal */
final readonly class ObjectRuleSetAssembler implements CaptureRuleSetAssembler
{
    /** @var ReflectionClass<object> */
    private ReflectionClass $reflectionClass;

    public function __construct(
        private object $message,
        private ?CaptureRule $parentRule = null,
    ) {
        $this->reflectionClass = new ReflectionClass($this->message::class);
    }

    /**
     * @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssemblerService
     *
     * @internal
     */
    public function assemble(CaptureRuleSetAssemblerService $propertyRuleSetAssemblerService): ?CaptureRule
    {
        if (!$this->isMarkedWithAnAttribute()) {
            return null;
        }

        $wrappedRuleSet = (new LazyRuleSet(
            /** @param LazyRuleSet<CompositeRuleSet> $lazyWrappedRuleSet */
            function (LazyRuleSet $lazyWrappedRuleSet) use ($propertyRuleSetAssemblerService): CompositeRuleSet {
                $objectRuleSet = new ObjectRuleSet(
                    $this->message,
                    $this->parentRule,
                    $lazyWrappedRuleSet,
                );

                return new CompositeRuleSet(
                    $objectRuleSet,
                    $this->getPropertyRules($objectRuleSet, $propertyRuleSetAssemblerService),
                );
            },
        ));

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

    /** @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssemblerService */
    private function getPropertyRules(ObjectRuleSet $objectRuleSet, CaptureRuleSetAssemblerService $propertyRuleSetAssemblerService): Generator
    {
        foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
            $propertyRuleSet = $propertyRuleSetAssemblerService
                ->assemble(new PropertyRuleSetAssembler($objectRuleSet, $reflectionProperty))
            ;

            if (null !== $propertyRuleSet) {
                yield $propertyRuleSet;
            }
        }
    }
}
