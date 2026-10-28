<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingPlan;

/**
 * @api
 *
 * @template T of object
 */
interface ObjectExceptionMappingPlanRegistry
{
    /**
     * @param class-string<T> $className
     *
     * @return null|ObjectExceptionMappingPlan<T>
     */
    public function getPlan(string $className): ?ObjectExceptionMappingPlan;
}
