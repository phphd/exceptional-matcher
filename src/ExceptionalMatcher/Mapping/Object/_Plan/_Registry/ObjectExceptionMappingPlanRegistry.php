<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry;

use Closure;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Compiler\ObjectExceptionMappingPlanCompiler;

use function array_key_exists;

/** @api */
final class ObjectExceptionMappingPlanRegistry
{
    /** @var array<class-string,?ObjectExceptionMappingPlan> */
    private array $plans = [];

    public function __construct(
        private readonly ObjectExceptionMappingPlanCompiler $planFactory,
        private ?Closure $autoloadClassNames,
    ) {
    }

    /** @param class-string $className */
    public function hasPlan(string $className): bool
    {
        return null !== $this->getPlan($className);
    }

    /** @param class-string $className */
    public function getPlan(string $className): ?ObjectExceptionMappingPlan
    {
        if (null !== $this->autoloadClassNames) {
            $this->autoloadClassNames->__invoke();
            $this->autoloadClassNames = null;
        }

        if (array_key_exists($className, $this->plans)) {
            return $this->plans[$className];
        }

        return $this->plans[$className] = $this->planFactory->create($className, $this);
    }
}
