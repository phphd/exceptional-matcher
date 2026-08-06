<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;

use function array_key_exists;

/**
 * @internal
 *
 * @template T of object
 *
 * @implements ObjectExceptionMappingPlanRegistry<T>
 */
final class MemoizingObjectExceptionMappingPlanRegistry implements ObjectExceptionMappingPlanRegistry
{
    /** @var array<class-string<T>,null|ObjectExceptionMappingPlan<T>> */
    private array $plans = [];

    public function __construct(
        /** @var ObjectExceptionMappingPlanRegistry<T> */
        private readonly ObjectExceptionMappingPlanRegistry $registry,
    ) {
    }

    /** @param class-string<T> $className */
    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    /**
     * @param class-string<T> $className
     *
     * @return null|ObjectExceptionMappingPlan<T>
     */
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
