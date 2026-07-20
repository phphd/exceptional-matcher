<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use Closure;
use PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan;
use ReflectionClass;

use function array_key_exists;

/** @api */
final class ClassMatchingPlanRegistry
{
    /** @var array<class-string,?ClassMappingPlan> */
    private array $plans = [];

    public function __construct(
        private readonly ClassMatchingPlanFactory $planFactory,
        private ?Closure $autoloadClassNames,
    ) {
    }

    /** @param class-string $className */
    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    /** @param class-string $className */
    public function getPlan(string $className): ?ClassMappingPlan
    {
        if (null !== $this->autoloadClassNames) {
            $this->autoloadClassNames->__invoke();
            $this->autoloadClassNames = null;
        }

        if (array_key_exists($className, $this->plans)) {
            return $this->plans[$className];
        }

        $reflectionClass = new ReflectionClass($className);

        if ([] === $reflectionClass->getAttributes(Try_::class)) {
            return $this->plans[$className] = null;
        }

        return $this->plans[$className] = $this->planFactory->create($reflectionClass, $this);
    }
}
