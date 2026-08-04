<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Traversable;

final class PropertyTypeAnalyser
{
    private const MATCHABLE_BUILTIN_TYPES = ['array', 'iterable', 'mixed', 'object'];

    public function __construct(
        private readonly ?ReflectionType $type,
    ) {
    }

    public function allowsMatchableObjects(ObjectExceptionMappingPlanRegistry $planRegistry): bool
    {
        return $this->canNamedTypeValueMatch($planRegistry)
            ?? $this->canCompositeTypeMatch($planRegistry)
            ?? true; // untyped property - the value may be anything
    }

    private function canCompositeTypeMatch(ObjectExceptionMappingPlanRegistry $planRegistry): ?bool
    {
        if (!$this->type instanceof ReflectionUnionType && !$this->type instanceof ReflectionIntersectionType) {
            return null;
        }

        foreach ($this->type->getTypes() as $reflectionType) {
            if ((new self($reflectionType))->allowsMatchableObjects($planRegistry)) {
                return true;
            }
        }

        return false;
    }

    private function canNamedTypeValueMatch(ObjectExceptionMappingPlanRegistry $planRegistry): ?bool
    {
        if (!$this->type instanceof ReflectionNamedType) {
            return null;
        }

        if ($this->type->isBuiltin()) {
            return in_array($this->type->getName(), self::MATCHABLE_BUILTIN_TYPES, true);
        }

        $className = $this->type->getName();

        if (!class_exists($className) && !interface_exists($className)) {
            // unresolvable type reference (e.g. relative `self`) - keep the property to stay on the safe side
            return true;
        }

        if (is_a($className, Traversable::class, true)) {
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
            // #[Try_] is not inherited, yet a plan-bearing subclass may still be assigned at runtime
            return true;
        }

        return $planRegistry->hasPlan($className);
    }
}
