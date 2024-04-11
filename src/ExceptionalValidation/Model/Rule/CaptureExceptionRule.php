<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownExceptions;

/** @internal */
final class CaptureExceptionRule implements CaptureRule
{
    public function __construct(
        private readonly CaptureRule $parent,
        private readonly MatchCondition $condition,
        private readonly string $message,
    ) {
    }

    public function capture(ThrownExceptions $thrownExceptions): array
    {
        $exception = $thrownExceptions->ejectWith($this->condition);

        if (null === $exception) {
            return [];
        }

        return [new CaughtException($exception, $this)];
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRoot(): object
    {
        return $this->parent->getRoot();
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getValue(): mixed
    {
        return $this->parent->getValue();
    }
}
