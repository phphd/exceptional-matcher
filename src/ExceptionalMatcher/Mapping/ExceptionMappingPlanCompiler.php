<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping;

/**
 * Compiles the mapping declared on a single reflection element into the plan that matches it.
 *
 * The mapping source is left untyped on purpose: each level of the mapping tree compiles its own kind of
 * reflection element, and only the interface makes them all proxyable behind one lazy service.
 *
 * @internal
 *
 * @template TSource of object
 *
 * @template-covariant TPlan of object
 */
interface ExceptionMappingPlanCompiler
{
    /**
     * @param TSource $mappingSource
     *
     * @return null|TPlan the mapping declares nothing to match, if null
     */
    public function compilePlan(object $mappingSource): ?object;
}
