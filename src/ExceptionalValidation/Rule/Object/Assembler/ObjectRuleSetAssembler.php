<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\ObjectRuleSet;
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
