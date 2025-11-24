<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory;
use ReflectionClass;

/** @internal */
final readonly class ObjectRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler<ObjectRulesAssemblerEnvelope> $objectRulesAssembler */
    public function __construct(
        private CaptureRuleSetAssembler $objectRulesAssembler,
    ) {
    }

    public static function create(): self
    {
        /** @var ArrayIterator<array-key,CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>> $captureListAssemblers */
        $captureListAssemblers = new ArrayIterator();
        $propertyRulesAssembler = new CompositeRuleSetAssembler($captureListAssemblers);
        $propertyRuleSetAssembler = new PropertyRuleSetAssembler($propertyRulesAssembler);

        $objectRulesAssembler = new ObjectRulesAssembler($propertyRuleSetAssembler);
        $objectRuleSetAssembler = new self($objectRulesAssembler);

        $captureListAssemblers->append(new PropertyCaptureRulesAssembler(CaptureMatchConditionFactory::create()));
        $captureListAssemblers->append(new PropertyNestedValidObjectRuleAssembler($objectRuleSetAssembler));
        $captureListAssemblers->append(new PropertyNestedValidIterableRulesAssembler(new IterableOfObjectsRuleSetAssembler($objectRuleSetAssembler)));

        return $objectRuleSetAssembler;
    }

    public function assemble(object $message, ?CaptureRule $parent = null): ?CaptureRule
    {
        $rules = null;
        $ruleSet = new LazyRuleSet(static function () use (&$rules): CaptureRule {
            /** @var CaptureRule $rules */
            return $rules;
        });

        $objectRuleSet = new ObjectRuleSet($message, $parent, $ruleSet);
        $envelope = new ObjectRulesAssemblerEnvelope(new ReflectionClass($message));

        $rules = $this->objectRulesAssembler->assemble($objectRuleSet, $envelope);

        if (null === $rules) {
            return null;
        }

        return $objectRuleSet;
    }
}
