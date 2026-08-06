<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler;

use Reflector;

/**
 * Compiles the mapping declared on a single reflection element into the plan that matches it.
 *
 * The mapping source is left untyped on purpose: each level of the mapping tree compiles its own kind of
 * reflection element, and only the interface makes them all proxyable behind one lazy service.
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
     * @param Reflector $reflector
     *
     * @return null|TPlan the mapping declares nothing to match, if null
     */
    public function compilePlan(Reflector $reflector): ?object;
}
