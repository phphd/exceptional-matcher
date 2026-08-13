<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\CatchExceptionMappingNode;
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
     * @param CatchExceptionMappingNode<Throwable> $catch
     *
     * @internal
     */
    public function match(CatchExceptionMappingNode $catch): bool
    {
        foreach ($this->remainingExceptions as $exceptionIndex => $exception) {
            if (!$catch->matches($exception)) {
                continue;
            }

            $this->reciprocateException($catch, $exceptionIndex);

            break;
        }

        return $this->isReciprocated();
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

    /** @param CatchExceptionMappingNode<Throwable> $catch */
    private function reciprocateException(CatchExceptionMappingNode $catch, int $exceptionIndex): void
    {
        $exception = $this->remainingExceptions[$exceptionIndex];

        unset($this->remainingExceptions[$exceptionIndex]);

        $this->matchedExceptions[] = new MatchedException($exception, $catch);
    }
}
