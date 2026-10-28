<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\Log;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * @internal
 *
 * @phpstan-type LogRecord array{level: mixed, message: string|Stringable, context: array<mixed>}
 */
final class SpyLogger extends AbstractLogger
{
    /** @var list<LogRecord> */
    private array $records = [];

    /**
     * @param string|Stringable $message
     * @param array<array-key,mixed> $context
     */
    public function log(mixed $level, $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    /** @return list<LogRecord> */
    public function flush(): array
    {
        try {
            return $this->records;
        } finally {
            $this->records = [];
        }
    }
}
