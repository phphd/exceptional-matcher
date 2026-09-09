<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Tests\Stub;

use Exception;
use Psr\Log\AbstractLogger;
use Stringable;

final class SpyLogger extends AbstractLogger
{
    /** @var list<array{level: mixed, message: string, context: array<mixed>}> */
    private array $records = [];

    /**
     * @param mixed $level
     * @param string|Stringable $message
     * @param array<mixed> $context
     */
    public function log($level, $message, array $context = []): void
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
