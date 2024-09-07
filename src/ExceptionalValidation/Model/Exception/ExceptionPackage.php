<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception;

use PhPhD\ExceptionalValidation\Model\Rule\CaptureExceptionRule;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class ExceptionPackage
{
    /** @var array<int,Throwable> */
    private array $remainingExceptions;

    /** @var list<CapturedException> */
    private array $capturedExceptions = [];

    /** @param list<Throwable> $exceptionList */
    public function __construct(array $exceptionList)
    {
        $this->remainingExceptions = $exceptionList;
    }

    public function processRule(CaptureExceptionRule $rule): void
    {
        foreach ($this->remainingExceptions as $index => $exception) {
            if ($rule->matches($exception)) {
                $this->captureException($index, $exception, $rule);

                return;
            }
        }
    }

    public function isProcessed(): bool
    {
        return [] === $this->remainingExceptions;
    }

    /** @return non-empty-list<CapturedException> */
    public function getCapturedExceptionsList(): array
    {
        Assert::notEmpty($this->capturedExceptions);

        return $this->capturedExceptions;
    }

    private function captureException(int $index, Throwable $exception, CaptureExceptionRule $rule): void
    {
        unset($this->remainingExceptions[$index]);

        $this->capturedExceptions[] = new CapturedException($exception, $rule);
    }
}
