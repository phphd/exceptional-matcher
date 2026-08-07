<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Plan\Compiler\Exception;

use RuntimeException;
use Throwable;

use function sprintf;

final class PropertyExceptionMappingPlanCompilationFailedException extends RuntimeException
{
    public function __construct(
        /** @var class-string */
        private readonly string $className,
        private readonly string $propertyName,
        Throwable $previous,
    ) {
        parent::__construct(
            sprintf(
                'Property %s::$%s exception mapping compilation has failed.',
                $this->className,
                $this->propertyName,
            ),
            previous: $previous,
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
