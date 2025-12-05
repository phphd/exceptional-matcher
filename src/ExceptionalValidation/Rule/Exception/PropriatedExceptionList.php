<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception;

use Countable;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use Throwable;

use function array_merge;
use function count;

final readonly class PropriatedExceptionList implements Countable
{
    public function __construct(
        /** @var list<PropriatedException<Throwable>> */
        private array $propriatedExceptions,
    ) {
    }

    /**
     * @template TResult
     *
     * @param PropriatedExceptionFormatter<Throwable,TResult> $formatter
     *
     * @return list<TResult>
     */
    public function format(PropriatedExceptionFormatter $formatter): array
    {
        /** @var list<list<TResult>> $results */
        $results = [];

        foreach ($this->propriatedExceptions as $propriatedException) {
            $results[] = $formatter->format($propriatedException);
        }

        return array_merge(...$results);
    }

    /** @return list<PropriatedException<Throwable>> */
    public function toArray(): array
    {
        return $this->propriatedExceptions;
    }

    public function count(): int
    {
        return count($this->propriatedExceptions);
    }
}
