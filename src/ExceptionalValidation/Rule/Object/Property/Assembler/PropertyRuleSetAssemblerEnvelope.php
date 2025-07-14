<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
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

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function getValue(object $message): mixed
    {
        if (!$this->reflectionProperty->isInitialized($message)) {
            return null;
        }

        return $this->reflectionProperty->getValue($message);
    }
}
