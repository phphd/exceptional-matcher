<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture;

use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;
use Throwable;

/** @internal */
final readonly class CaptureExceptionRule implements CaptureRule
{
    public function __construct(
        private CaptureRule $parent,
        /** @var MatchCondition<Throwable> */
        private MatchCondition $condition,
        private ?string $messageTemplate,
        /** @var class-string */
        private string $formatterId,
    ) {
    }

    public function process(ExceptionPackage $package): bool
    {
        $package->processRule($this);

        return $package->isProcessed();
    }

    public function getParent(): CaptureRule
    {
        return $this->parent;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->parent->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->parent->getValue();
    }

    public function matchesException(Throwable $exception): bool
    {
        return $this->condition->matches($exception);
    }

    public function getMessageTemplate(): ?string
    {
        return $this->messageTemplate;
    }

    public function getFormatterId(): string
    {
        return $this->formatterId;
    }
}
