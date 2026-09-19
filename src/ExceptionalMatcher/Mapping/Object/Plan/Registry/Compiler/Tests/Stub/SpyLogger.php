<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Tests\Stub;

use Psr\Log\AbstractLogger;
use Stringable;

final class SpyLogger extends AbstractLogger
{
    /** @var list<array{level: mixed, message: string, context: array<mixed>}> */
    private array $records = [];

    /**
     * @param string|Stringable $message
     * @param array<array-key,mixed> $context
     */
    public function log(mixed $level, $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string)$message,
            'context' => $context,
        ];
    }

    /** @return list<array{level: mixed, message: string, context: array<mixed>}> */
    public function flush(): array
    {
        try {
            return $this->records;
        } finally {
            $this->records = [];
        }
    }
}
