<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class ExceptionReciprocal
{
    /** @var array<int,Throwable> */
    private array $remainingExceptions;

    /** @var list<ExceptionMatch<Throwable>> */
    private array $exceptionMatches = [];

    /** @param non-empty-list<Throwable> $remainingExceptions */
    public function __construct(array $remainingExceptions)
    {
        $this->remainingExceptions = $remainingExceptions;
    }

    /**
     * @param CatchExceptionMappingNode<Throwable> $catch
     *
     * @internal
     */
    public function match(CatchExceptionMappingNode $catch): bool
    {
        foreach ($this->remainingExceptions as $exceptionIndex => $exception) {
            if ($catch->matches($exception)) {
                $this->reciprocateException($catch, $exceptionIndex);

                break;
            }
        }

        return $this->isReciprocated();
    }

    public function isReciprocated(): bool
    {
        return [] === $this->remainingExceptions;
    }

    public function getMatches(): ExceptionMatchList
    {
        Assert::notEmpty($this->exceptionMatches);

        return new ExceptionMatchList($this->exceptionMatches);
    }

    /** @param CatchExceptionMappingNode<Throwable> $catch */
    private function reciprocateException(CatchExceptionMappingNode $catch, int $exceptionIndex): void
    {
        $exception = $this->remainingExceptions[$exceptionIndex];

        unset($this->remainingExceptions[$exceptionIndex]);

        $this->exceptionMatches[] = new ExceptionMatch($exception, $catch);
    }
}
