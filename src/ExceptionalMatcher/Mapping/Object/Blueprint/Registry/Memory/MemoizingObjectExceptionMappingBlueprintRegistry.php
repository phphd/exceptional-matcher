<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;

use function array_key_exists;

/**
 * @internal
 *
 * @template T of object
 *
 * @implements ObjectExceptionMappingBlueprintRegistry<T>
 */
final class MemoizingObjectExceptionMappingBlueprintRegistry implements ObjectExceptionMappingBlueprintRegistry
{
    /** @var array<class-string<T>,null|ObjectExceptionMappingBlueprint<T>> */
    private array $plans = [];

    /** @api */
    public function __construct(
        /** @var ObjectExceptionMappingBlueprintRegistry<T> */
        private readonly ObjectExceptionMappingBlueprintRegistry $registry,
    ) {
    }

    /**
     * @param class-string<T> $className
     *
     * @return null|ObjectExceptionMappingBlueprint<T>
     */
    public function getMappingBlueprint(string $className): ?ObjectExceptionMappingBlueprint
    {
        if (array_key_exists($className, $this->plans)) {
            return $this->plans[$className];
        }

        return $this->plans[$className] = $this->registry->getMappingBlueprint($className);
    }

    /** @api */
    public function clear(): void
    {
        $this->plans = [];
    }
}
