<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use ReflectionProperty;

/** @internal */
final class PropertyRuleSetAssemblerEnvelope implements CaptureRuleSetAssemblerEnvelope
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
