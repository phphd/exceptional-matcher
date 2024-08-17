<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception;

use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;
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

    public function __construct(ThrownException $exception)
    {
        $this->remainingExceptions = $exception->getExceptions();
    }

    /** @return non-empty-list<CapturedException> */
    public function getCapturedExceptionsList(): array
    {
        Assert::notEmpty($this->capturedExceptions);

        return $this->capturedExceptions;
    }

    public function isProcessed(): bool
    {
        return [] === $this->remainingExceptions;
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

    private function captureException(int $index, Throwable $exception, CaptureExceptionRule $rule): void
    {
        unset($this->remainingExceptions[$index]);

        $this->capturedExceptions[] = new CapturedException($exception, $rule);
    }
}
