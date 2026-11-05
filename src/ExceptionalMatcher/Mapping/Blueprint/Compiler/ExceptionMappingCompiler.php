<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\ExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use Psr\Log\LoggerInterface;
use Reflector;

/**
 * Compiles the mapping declared on a single reflection element into an exception-matching print.
 *
 * @internal
 *
 * @template TReflector of Reflector
 *
 * @template TPrint of ExceptionMappingBlueprint
 */
interface ExceptionMappingCompiler
{
    /** @return static<TReflector,TPrint> */
    public function reportingTo(LoggerInterface $reporter): static;

    /**
     * @param TReflector $reflector
     *
     * @return null|TPrint
     */
    public function compile(Reflector $reflector): ?ExceptionMappingBlueprint;

    /** @param TPrint $blueprint */
    public function forceCompilationCompletion(ExceptionMappingBlueprint $blueprint): void;
}
