<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;

/**
 * @api
 *
 * @template T of object
 */
interface ObjectExceptionMappingBlueprintRegistry
{
    /**
     * @param class-string<T> $className
     *
     * @return null|ObjectExceptionMappingBlueprint<T>
     */
    public function getMappingBlueprint(string $className): ?ObjectExceptionMappingBlueprint;
}
