<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use ReflectionClass;

/** @internal */
final class ObjectRulesAssemblerEnvelope implements CaptureRuleSetAssemblerEnvelope
{
    public function __construct(
        /** @var ReflectionClass<object> */
        private readonly ReflectionClass $reflectionClass,
    ) {
    }

    /** @return ReflectionClass<object> */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }
}
