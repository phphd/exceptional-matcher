<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\_Exception;

use RuntimeException;
use Throwable;

final class CatchExceptionMappingPlanCompilationFailedException extends RuntimeException
{
    public function __construct(
        /** @var ?class-string */
        private readonly ?string $className,
        private readonly ?string $propertyName,
        Throwable $previous,
    ) {
        parent::__construct('#[Catch_] attribute compilation has failed.', previous: $previous);
    }

    /** @return ?class-string */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }
}
