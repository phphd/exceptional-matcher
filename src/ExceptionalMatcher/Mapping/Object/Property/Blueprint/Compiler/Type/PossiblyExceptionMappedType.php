<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Type;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\AnyType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedIterableType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedObjectType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\TypeSpecification;
use ReflectionClass;
use ReflectionNamedType;

/** @internal */
final class PossiblyExceptionMappedType implements TypeSpecification
{
    private AnyType $mixedTypesSpecification;

    public function __construct(
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $blueprintRegistry,
    ) {
        $this->mixedTypesSpecification = new AnyType([
            new MixedType(),
            new MixedObjectType(),
            new MixedIterableType(),
        ]);
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        if ($this->mixedTypesSpecification->isSatisfiedBy($type)) {
            return true;
        }

        $className = $type->getName();
        if (in_array($className, ['self', 'parent'], strict: true)) { // Trait-declared properties
            return true;
        }

        if ($type->isBuiltin()) {
            return false;
        }

        /** @var class-string $className */

        $reflectionClass = new ReflectionClass($className);

        return match(true) {
            // Enums don't allow properties
            $reflectionClass->isEnum(),
            // Built-in classes / interface's implementations go w/o #[Try_]
            $reflectionClass->isInternal() => false,
            // Interfaces might have any implementor - including those with #[Try_]
            $reflectionClass->isInterface(),
            // Non-final classes might have any extender - including those with #[Try_]
            !$reflectionClass->isFinal() => true,
            default => null !== $this->blueprintRegistry->getMappingBlueprint($className)
        };
    }
}
