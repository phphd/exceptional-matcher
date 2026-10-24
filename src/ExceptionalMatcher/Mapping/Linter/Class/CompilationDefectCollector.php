<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\Log\SpyLogger;

use function array_column;
use function array_filter;
use function array_map;

/** @internal */
final class CompilationDefectCollector
{
    /** @api */
    public function __construct(
        private readonly SpyLogger $compilationLogger,
    ) {
    }

    /** @return list<MappingDefect> */
    public function flushDefects(): array
    {
        $exceptions = $this->compilationExceptions();

        $locations = array_map(self::defectLocationOf(...), $exceptions);

        return array_map(MappingDefect::error(...), $locations, $exceptions);
    }

    /** @return array<ExceptionMappingPlanCompilationFailedException> */
    private function compilationExceptions(): array
    {
        return array_filter(
            $this->flushLoggedExceptions(),
            static fn (mixed $exception): bool => $exception instanceof ExceptionMappingPlanCompilationFailedException,
        );
    }

    /** @return list<mixed> */
    private function flushLoggedExceptions(): array
    {
        return array_column(array_column($this->compilationLogger->flush(), 'context'), 'exception');
    }

    private static function defectLocationOf(ExceptionMappingPlanCompilationFailedException $exception): DefectLocation
    {
        return match (true) {
            $exception instanceof CatchExceptionMappingPlanCompilationFailedException => new DefectLocation(
                $exception->getClassName(),
                $exception->getPropertyName()
            ),
            $exception instanceof PropertyExceptionMappingPlanCompilationFailedException => new DefectLocation(
                $exception->getClassName(),
                $exception->getPropertyName()
            ),
            $exception instanceof ObjectExceptionMappingPlanCompilationFailedException => new DefectLocation(
                $exception->getClassName()
            ),
            default => throw new LogicException('Defect location is not known for: '.$exception::class),
        };
    }
}
