<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Class;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;

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
            static fn(mixed $exception): bool => $exception instanceof ExceptionMappingPrintCompilationFailedException,
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
                $exception->getPropertyName(),
            ),
            $exception instanceof PropertyExceptionMappingPrintCompilationFailedException => new DefectLocation(
                $exception->getClassName(),
                $exception->getPropertyName(),
            ),
            $exception instanceof ObjectExceptionMappingPrintCompilationFailedException => new DefectLocation(
                $exception->getClassName(),
            ),
            default => throw new LogicException('Defect location is not known for: '.$exception::class),
        };
    }
}
