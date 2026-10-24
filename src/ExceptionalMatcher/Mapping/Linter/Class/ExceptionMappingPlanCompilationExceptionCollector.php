<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class;

use Exception;
use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompilationFailedException;
use Psr\Log\AbstractLogger;

use function array_map;

/** @internal */
final class ExceptionMappingPlanCompilationExceptionCollector extends AbstractLogger
{
    /** @var list<ExceptionMappingPlanCompilationFailedException> */
    private array $exceptions = [];

    /**
     * @param array<array-key,mixed> $context
     *
     * @override the parameters stay `mixed` to remain compatible with psr/log 1, 2 and 3 alike
     */
    public function log(mixed $level, mixed $message, array $context = []): void
    {
        $exception = $context['exception'] ?? null;

        if (!$exception instanceof ExceptionMappingPlanCompilationFailedException) {
            return;
        }

        $this->exceptions[] = $exception;
    }

    /** @return list<MappingDefect> */
    public function flush(): array
    {
        try {
            $locations = array_map(self::defectLocationOf(...), $this->exceptions);

            return array_map(MappingDefect::error(...), $locations, $this->exceptions);
        } finally {
            $this->exceptions = [];
        }
    }

    private static function defectLocationOf(ExceptionMappingPlanCompilationFailedException $exception): DefectLocation
    {
        if ($exception instanceof CatchExceptionMappingPlanCompilationFailedException) {
            return new DefectLocation($exception->getClassName(), $exception->getPropertyName());
        }

        if ($exception instanceof PropertyExceptionMappingPlanCompilationFailedException) {
            return new DefectLocation($exception->getClassName(), $exception->getPropertyName());
        }

        if ($exception instanceof ObjectExceptionMappingPlanCompilationFailedException) {
            return new DefectLocation($exception->getClassName());
        }

        throw new LogicException('Defect location is not known for: '.$exception::class);
    }
}
