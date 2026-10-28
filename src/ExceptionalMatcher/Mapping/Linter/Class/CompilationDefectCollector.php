<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;

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

    /** @return array<ExceptionMappingPrintCompilationFailedException> */
    private function compilationExceptions(): array
    {
        return array_filter(
            $this->flushLoggedExceptions(),
            static fn (mixed $exception): bool => $exception instanceof ExceptionMappingPrintCompilationFailedException,
        );
    }

    /** @return list<mixed> */
    private function flushLoggedExceptions(): array
    {
        return array_column(array_column($this->compilationLogger->flush(), 'context'), 'exception');
    }

    private static function defectLocationOf(ExceptionMappingPrintCompilationFailedException $exception): DefectLocation
    {
        return match (true) {
            $exception instanceof CatchExceptionMappingPrintCompilationFailedException => new DefectLocation(
                $exception->getClassName(),
                $exception->getPropertyName()
            ),
            $exception instanceof PropertyExceptionMappingPrintCompilationFailedException => new DefectLocation(
                $exception->getClassName(),
                $exception->getPropertyName()
            ),
            $exception instanceof ObjectExceptionMappingPrintCompilationFailedException => new DefectLocation(
                $exception->getClassName()
            ),
            default => throw new LogicException('Defect location is not known for: '.$exception::class),
        };
    }
}
