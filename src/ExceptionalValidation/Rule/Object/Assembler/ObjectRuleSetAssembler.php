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

    private function __construct(
        private object $message,
    ) {
        $this->reflectionClass = new ReflectionClass($this->message::class);
    }

    public static function createForMessage(object $message): ?self
    {
        $envelope = new self($message);

        if (!$envelope->isMarkedWithAnAttribute()) {
            return null;
        }

        return $envelope;
    }

    /**
     * @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssembler
     *
     * @internal
     */
    public function assemble(?CaptureRule $parent, CaptureRuleSetAssemblerService $propertyRuleSetAssembler): ?CaptureRule
    {
        $wrappedRuleSet = (new LazyRuleSet(
            /** @param LazyRuleSet<CompositeRuleSet> $lazyWrappedRuleSet */
            function (LazyRuleSet $lazyWrappedRuleSet) use ($parent, $propertyRuleSetAssembler): CompositeRuleSet {
                $objectRuleSet = new ObjectRuleSet(
                    $this->message,
                    $parent,
                    $lazyWrappedRuleSet,
                );

                return new CompositeRuleSet(
                    $objectRuleSet,
                    $this->getPropertyRules($objectRuleSet, $propertyRuleSetAssembler),
                );
            },
        ));

        return $wrappedRuleSet->build()?->getParent();
    }

    private function isMarkedWithAnAttribute(): bool
    {
        return [] !== $this->reflectionClass->getAttributes(ExceptionalValidation::class);
    }

    /** @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssembler */
    private function getPropertyRules(ObjectRuleSet $objectRuleSet, CaptureRuleSetAssemblerService $propertyRuleSetAssembler): Generator
    {
        foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
            $propertyRuleSetAssemblerEnvelope = new PropertyRuleSetAssembler($reflectionProperty);

            $propertyRuleSet = $propertyRuleSetAssembler->assemble(
                $objectRuleSet,
                $propertyRuleSetAssemblerEnvelope,
            );

            if (null !== $propertyRuleSet) {
                yield $propertyRuleSet;
            }
        }
    }
}
