<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Defect;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\_Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\_Exception\PropertyExceptionMappingPlanCompilationFailedException;
use Psr\Log\AbstractLogger;
use Throwable;

/**
 * Turns the mapping compilers' degraded-compilation records into lint defects.
 *
 * Handed to the compilers instead of the application logger, this is how the linter learns about every
 * mapping they had to drop: each record carries the wrapper exception, and the wrapper knows its own location.
 *
 * @internal
 */
final class MappingDefectCollector extends AbstractLogger
{
    /** @var list<MappingDefect> */
    private array $defects = [];

    /**
     * @param array<array-key,mixed> $context
     *
     * @override the parameters stay `mixed` to remain compatible with psr/log 1, 2 and 3 alike
     */
    public function log(mixed $level, mixed $message, array $context = []): void
    {
        $exception = $context['exception'] ?? null;

        if (!$exception instanceof Throwable) {
            return;
        }

        $location = self::defectLocationOf($exception);

        if (null === $location) {
            return;
        }

        $this->defects[] = MappingDefect::error($location, $exception);
    }

    /** @return list<MappingDefect> */
    public function flush(): array
    {
        $defects = $this->defects;

        $this->defects = [];

        return $defects;
    }

    private static function defectLocationOf(Throwable $exception): ?DefectLocation
    {
        if ($exception instanceof PropertyExceptionMappingPlanCompilationFailedException) {
            $reflectionProperty = $exception->getReflectionProperty();

            return new DefectLocation(
                $reflectionProperty->getDeclaringClass()
                    ->getName(),
                $reflectionProperty->getName(),
            );
        }

        if ($exception instanceof ObjectExceptionMappingPlanCompilationFailedException) {
            return new DefectLocation($exception->getClassName());
        }

        return null;
    }
}
