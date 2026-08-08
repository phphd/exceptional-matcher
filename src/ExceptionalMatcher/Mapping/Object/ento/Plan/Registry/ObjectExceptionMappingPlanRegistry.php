<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\ObjectExceptionMappingPlan;

/**
 * @api
 *
 * @template T of object
 */
interface ObjectExceptionMappingPlanRegistry
{
    /** @param class-string<T> $className */
    public function hasPlan(string $className): bool;

    /**
     * @param class-string<T> $className
     *
     * @return null|ObjectExceptionMappingPlan<T>
     */
    public function getPlan(string $className): ?ObjectExceptionMappingPlan;
}
