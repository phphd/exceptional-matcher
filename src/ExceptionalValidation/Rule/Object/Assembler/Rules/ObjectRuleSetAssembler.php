<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<ObjectRuleSetAssemblerEnvelope>
 */
final readonly class ObjectRuleSetAssembler implements CaptureRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope> $propertyRuleSetAssembler */
    public function __construct(
        private CaptureRuleSetAssembler $propertyRuleSetAssembler,
    ) {
    }

    public static function create(): self
    {
        /** @var ArrayIterator<array-key,CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>> $captureListAssemblers */
        $captureListAssemblers = new ArrayIterator();
        $propertyRulesAssembler = new CompositeRuleSetAssembler($captureListAssemblers);
        $propertyRuleSetAssembler = new PropertyRuleSetAssembler($propertyRulesAssembler);

        $objectRuleSetAssembler = new self($propertyRuleSetAssembler);

        $captureListAssemblers->append(new PropertyCaptureRulesAssembler(CaptureMatchConditionFactory::create()));
        $captureListAssemblers->append(new PropertyNestedValidObjectRuleAssembler($objectRuleSetAssembler));
        $captureListAssemblers->append(new PropertyNestedValidIterableRulesAssembler(new IterableOfObjectsRuleSetAssembler($objectRuleSetAssembler)));

        return $objectRuleSetAssembler;
    }

    public function assembleForMessage(object $message, ?CaptureRule $parentRule = null): ?CaptureRule
    {
        $envelope = new ObjectRuleSetAssemblerEnvelope($message);

        return $this->assemble($parentRule, $envelope);
    }

    /** @param ObjectRuleSetAssemblerEnvelope $envelope */
    public function assemble(?CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        return $envelope->assemble($parentRule, $this->propertyRuleSetAssembler);
    }
}
