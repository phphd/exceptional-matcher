<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use ReflectionProperty;

/** @internal */
final class PropertyRulesAssemblerEnvelope implements CaptureRuleSetAssemblerEnvelope
{
    public function __construct(
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    public function getReflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }
}
