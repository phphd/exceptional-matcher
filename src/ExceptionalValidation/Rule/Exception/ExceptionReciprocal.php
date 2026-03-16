<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\MatchExceptionRule;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class ExceptionReciprocal
{
    /** @var array<int,Throwable> */
    private array $remainingExceptions;

    /** @var list<MatchedException<Throwable>> */
    private array $matchedExceptions = [];

    /** @param non-empty-list<Throwable> $remainingExceptions */
    public function __construct(array $remainingExceptions)
    {
        $this->remainingExceptions = $remainingExceptions;
    }

    /**
     * @param MatchExceptionRule<Throwable> $rule
     *
     * @internal
     */
    public function process(MatchExceptionRule $rule): void
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

    public function getMatchedExceptionList(): MatchedExceptionList
    {
        Assert::notEmpty($this->matchedExceptions);

        return new MatchedExceptionList($this->matchedExceptions);
    }

    /** @param MatchExceptionRule<Throwable> $rule */
    private function reciprocateException(int $index, Throwable $exception, MatchExceptionRule $rule): void
    {
        unset($this->remainingExceptions[$index]);

        $this->matchedExceptions[] = new MatchedException($exception, $rule);
    }
}
