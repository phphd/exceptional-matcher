<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;

use function array_key_exists;

final class MemoizingObjectExceptionMappingPlanRegistry implements ObjectExceptionMappingPlanRegistry
{
    /** @var array<class-string,?ObjectExceptionMappingPlan> */
    private array $plans = [];

    public function __construct(
        private readonly ObjectExceptionMappingPlanRegistry $registry,
    ) {
    }

    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    public function getPlan(string $className): ?ObjectExceptionMappingPlan
    {
        if (array_key_exists($className, $this->plans)) {
            return $this->plans[$className];
        }

        return $this->plans[$className] = $this->registry->getPlan($className);
    }

    public function clear(): void
    {
        $this->plans = [];
    }
}
