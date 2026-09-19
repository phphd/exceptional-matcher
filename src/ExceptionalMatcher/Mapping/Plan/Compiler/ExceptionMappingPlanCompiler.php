<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler;

use Psr\Log\LoggerInterface;
use Reflector;

/**
 * Compiles the mapping declared on a single reflection element into an exception-matching plan.
 *
 * @internal
 *
 * @template TReflector of Reflector
 *
 * @template-covariant TPlan of object
 */
interface ExceptionMappingPlanCompiler
{
    public function reportingTo(LoggerInterface $reporter): static;

    /**
     * @param TReflector $reflector
     *
     * @return null|TPlan
     */
    public function compilePlan(Reflector $reflector): ?object;
}
