<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use Psr\Log\AbstractLogger;
use Throwable;

/** @internal */
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
        if ($exception instanceof CatchExceptionMappingPlanCompilationFailedException) {
            // #[Catch_] that was compiled apart from the property is reported on the attribute itself
            $className = $exception->getClassName() ?? Catch_::class;

            return new DefectLocation($className, $exception->getPropertyName());
        }

        if ($exception instanceof PropertyExceptionMappingPlanCompilationFailedException) {
            return new DefectLocation($exception->getClassName(), $exception->getPropertyName());
        }

        if ($exception instanceof ObjectExceptionMappingPlanCompilationFailedException) {
            return new DefectLocation($exception->getClassName());
        }

        return null;
    }
}
