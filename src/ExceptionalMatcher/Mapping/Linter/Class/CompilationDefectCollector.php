<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;

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
