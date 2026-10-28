<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Traversable;

use function class_exists;
use function in_array;
use function interface_exists;
use function is_a;

final class PropertyTypeAnalyser
{
    private const MATCHABLE_BUILTIN_TYPES = ['array', 'iterable', 'mixed', 'object'];

    public function __construct(
        private readonly ?ReflectionType $type,
    ) {
    }

    /** @param ObjectExceptionMappingBlueprintRegistry<object> $printRegistry */
    public function allowsMatchableObjects(ObjectExceptionMappingBlueprintRegistry $printRegistry): bool
    {
        return $this->canNamedTypeValueMatch($printRegistry)
            ?? $this->canCompositeTypeMatch($printRegistry)
            ?? true; // untyped property - the value may be anything
    }

    /** @param ObjectExceptionMappingBlueprintRegistry<object> $printRegistry */
    private function canCompositeTypeMatch(ObjectExceptionMappingBlueprintRegistry $printRegistry): ?bool
    {
        if (!$this->type instanceof ReflectionUnionType && !$this->type instanceof ReflectionIntersectionType) {
            return null;
        }

        foreach ($this->type->getTypes() as $reflectionType) {
            if ((new self($reflectionType))->allowsMatchableObjects($printRegistry)) {
                return true;
            }
        }

        return false;
    }

    /** @param ObjectExceptionMappingBlueprintRegistry<object> $printRegistry */
    private function canNamedTypeValueMatch(ObjectExceptionMappingBlueprintRegistry $printRegistry): ?bool
    {
        if (!$this->type instanceof ReflectionNamedType) {
            return null;
        }

        if ($this->type->isBuiltin()) {
            return in_array($this->type->getName(), self::MATCHABLE_BUILTIN_TYPES, strict: true);
        }

        $className = $this->type->getName();

        if (!class_exists($className) && !interface_exists($className)) {
            // unresolvable type reference (e.g. relative `self`) - keep the property to stay on the safe side
            return true;
        }

        if (is_a($className, Traversable::class, allow_string: true)) {
            return true;
        }

        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isInternal() || $reflectionClass->isEnum()) {
            // built-in classes / interface implementations cannot declare #[Try_]
            return false;
        }

        if ($reflectionClass->isInterface()) {
            // any implementor - including one bearing #[Try_] - may be assigned
            return true;
        }

        if (!$reflectionClass->isFinal()) {
            // #[Try_] is not inherited, yet a print-bearing subclass may still be assigned at runtime
            return true;
        }

        return null !== $printRegistry->getMappingBlueprint($className);
    }
}
