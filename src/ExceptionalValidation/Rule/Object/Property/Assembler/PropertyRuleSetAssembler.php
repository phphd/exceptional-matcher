<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use ReflectionProperty;

/** @internal */
final class PropertyRuleSetAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private readonly ObjectRuleSet $parentRule,
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    public function assemble(PropertyRuleSetAssemblerService $service): ?CaptureRule
    {
        $captureRuleSet = new LazyRuleSet(
            /** @param LazyRuleSet<CaptureRule> $lazyCaptureRuleSet */
            function (LazyRuleSet $lazyCaptureRuleSet) use ($service): ?CaptureRule {
                $object = $this->parentRule->getValue();

                $propertyRuleSet = new PropertyRuleSet(
                    $this->parentRule,
                    $this->getName(),
                    $this->getPropertyValue($object),
                    $lazyCaptureRuleSet,
                );

                return $service->captureListAssemblerService
                    ->assemble(new PropertyCaptureRulesAssembler($propertyRuleSet, $this->reflectionProperty))
                ;
            },
        );

        return $captureRuleSet->build()?->getParent();
    }

    public function getParentRule(): ObjectRuleSet
    {
        return $this->parentRule;
    }

    private function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    private function getPropertyValue(object $message): mixed
    {
        if (!$this->reflectionProperty->isInitialized($message)) {
            return null;
        }

        return $this->reflectionProperty->getValue($message);
    }
}
