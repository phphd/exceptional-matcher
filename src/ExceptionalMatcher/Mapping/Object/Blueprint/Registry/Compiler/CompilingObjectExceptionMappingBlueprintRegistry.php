<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 *
 * @template T of object
 *
 * @implements ObjectExceptionMappingBlueprintRegistry<T>
 */
final class CompilingObjectExceptionMappingBlueprintRegistry implements ObjectExceptionMappingBlueprintRegistry
{
    /** @api */
    public function __construct(
        /** @var ExceptionMappingCompiler<ReflectionClass<T>,ObjectExceptionMappingBlueprint<T>> */
        private readonly ExceptionMappingCompiler $compiler,
    ) {
    }

    /**
     * @param class-string<T> $className
     *
     * @throws ReflectionException
     * @return null|ObjectExceptionMappingBlueprint<T>
     *
     */
    public function getMappingBlueprint(string $className): ?ObjectExceptionMappingBlueprint
    {
        return $this->compiler->compileBlueprint(new ReflectionClass($className));
    }
}
