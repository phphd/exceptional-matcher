<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Class;

use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\MappingDefect;

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
        return new DefectLocation($exception->getClassName(), $exception->getPropertyName());
    }
}
