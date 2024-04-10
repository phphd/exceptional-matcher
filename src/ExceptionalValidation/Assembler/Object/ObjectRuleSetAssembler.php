<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\ObjectRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Model\Rule\ObjectRuleSet;
use ReflectionClass;

/** @internal */
final class ObjectRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler< ObjectRulesAssemblerEnvelope> $objectRulesAssembler */
    public function __construct(
        private readonly CaptureRuleSetAssembler $objectRulesAssembler,
    ) {
    }

    public function assemble(object $message, ?CaptureRule $parent = null): ?CaptureRule
    {
        /** @var CaptureRule $rules */
        $rules = null;
        $ruleSet = new LazyRuleSet(static function () use (&$rules): CaptureRule {
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
