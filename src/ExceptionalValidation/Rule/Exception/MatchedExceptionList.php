<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception;

use Countable;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use Throwable;

use function array_merge;
use function count;

final class MatchedExceptionList implements Countable
{
    public function __construct(
        /** @var list<MatchedException<Throwable>> */
        private readonly array $matchedExceptions,
    ) {
    }

    /**
     * @template TResult
     *
     * @param MatchedExceptionFormatter<Throwable,TResult> $formatter
     *
     * @return list<TResult>
     */
    public function format(MatchedExceptionFormatter $formatter): array
    {
        /** @var list<list<TResult>> $results */
        $results = [];

        foreach ($this->matchedExceptions as $matchedException) {
            $results[] = $formatter->format($matchedException);
        }

        return array_merge(...$results);
    }

    /** @return list<MatchedException<Throwable>> */
    public function toArray(): array
    {
        return $this->matchedExceptions;
    }

    public function count(): int
    {
        return count($this->matchedExceptions);
    }
}
