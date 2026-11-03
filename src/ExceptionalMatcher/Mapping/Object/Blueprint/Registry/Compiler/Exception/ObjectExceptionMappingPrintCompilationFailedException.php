<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use ReflectionClass;
use Throwable;

use function sprintf;

/** @internal */
final class ObjectExceptionMappingPrintCompilationFailedException extends ExceptionMappingPrintCompilationFailedException
{
    public function __construct(
        /** @var ReflectionClass<object> */
        private readonly ReflectionClass $class,
        Throwable $previous,
    ) {
        parent::__construct(
            'Class exception mapping compilation has failed.',
            previous: $previous,
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->class->getName();
    }
}
