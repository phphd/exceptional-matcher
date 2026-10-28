<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler;

use Psr\Log\LoggerInterface;
use Reflector;

/**
 * Compiles the mapping declared on a single reflection element into an exception-matching plan.
 *
 * @internal
 *
 * @template TReflector of Reflector
 *
 * @template-covariant TPrint of object
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
    public function compilePrint(Reflector $reflector): ?object;
}
