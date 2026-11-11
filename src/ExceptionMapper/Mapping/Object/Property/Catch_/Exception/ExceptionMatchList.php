<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception;

use Countable;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use Throwable;

use function array_merge;
use function count;

/** @api */
final class ExceptionMatchList implements Countable
{
    public function __construct(
        /** @var list<ExceptionMatch<Throwable>> */
        private readonly array $exceptionMatches,
    ) {
    }

    /**
     * @template TResult
     *
     * @param ExceptionMatchFormatter<Throwable,TResult> $formatter
     *
     * @return list<TResult>
     */
    public function format(ExceptionMatchFormatter $formatter): array
    {
        /** @var list<list<TResult>> $results */
        $results = [];

        foreach ($this->exceptionMatches as $match) {
            $results[] = $formatter->format($match);
        }

        return array_merge(...$results);
    }

    /**
     * @internal
     * @codeCoverageIgnore
     *
     * @return list<ExceptionMatch<Throwable>>
     */
    public function toArray(): array
    {
        return $this->exceptionMatches;
    }

    public function count(): int
    {
        return count($this->exceptionMatches);
    }
}
