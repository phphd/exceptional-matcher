<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;

/** @api */
interface ObjectExceptionMappingPlanRegistry
{
    /** @param class-string $className */
    public function hasPlan(string $className): bool;

    /** @param class-string $className */
    public function getPlan(string $className): ?ObjectExceptionMappingPlan;
}
