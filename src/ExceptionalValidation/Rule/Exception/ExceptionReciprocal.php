<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule;
use Throwable;
use Webmozart\Assert\Assert;

/** @api */
final class ExceptionReciprocal
{
    /** @var array<int,Throwable> */
    private array $remainingExceptions;

    /** @var list<PropriatedException<Throwable>> */
    private array $propriatedExceptions = [];

    /** @param non-empty-list<Throwable> $remainingExceptions */
    public function __construct(array $remainingExceptions)
    {
        $this->remainingExceptions = $remainingExceptions;
    }

    /** @internal */
    public function process(CaptureExceptionRule $rule): void
    {
        foreach ($this->remainingExceptions as $index => $exception) {
            if ($rule->matchesException($exception)) {
                $this->reciprocateException($index, $exception, $rule);

                return;
            }
        }
    }

    public function isReciprocated(): bool
    {
        return [] === $this->remainingExceptions;
    }

    public function getPropriatedExceptionList(): PropriatedExceptionList
    {
        Assert::notEmpty($this->propriatedExceptions);

        return new PropriatedExceptionList($this->propriatedExceptions);
    }

    private function reciprocateException(int $index, Throwable $exception, CaptureExceptionRule $rule): void
    {
        unset($this->remainingExceptions[$index]);

        $this->propriatedExceptions[] = new PropriatedException($exception, $rule);
    }
}
