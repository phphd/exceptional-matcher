<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler;

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
    /**
     * @param TReflector $reflector
     *
     * @return null|TPlan the mapping declares nothing to match, if null
     */
    public function compilePlan(Reflector $reflector): ?object;
}
