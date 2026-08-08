<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Compiler\Exception;

use RuntimeException;
use Throwable;

use function sprintf;

final class ObjectExceptionMappingPlanCompilationFailedException extends RuntimeException
{
    /** @param class-string $className */
    public function __construct(
        private readonly string $className,
        Throwable $previous,
    ) {
        parent::__construct(
            sprintf('Class %s exception mapping compilation has failed.', $className),
            previous: $previous,
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }
}
