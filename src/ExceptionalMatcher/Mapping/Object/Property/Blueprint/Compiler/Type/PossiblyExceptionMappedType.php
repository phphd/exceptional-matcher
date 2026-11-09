<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Type;

use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\AnyType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\TypeSpecification;
use ReflectionClass;
use ReflectionNamedType;

use function in_array;

/** @internal */
final class PossiblyExceptionMappedType implements TypeSpecification
{
    public function __construct(
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $blueprintRegistry,
    ) {
    }

    /** @codeCoverageIgnore */
    public function __invoke(mixed $value): bool
    {
        if (PropertyExceptionMappingBlueprint::isIterable()($value)) {
            return true;
        }

        if (PropertyExceptionMappingBlueprint::isObject()($value)) {
            return null !== $this->blueprintRegistry->getMappingBlueprint($value::class);
        }

        return false;
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        if (self::isObjectOrIterableOrMixed()->isSatisfiedBy($type)) {
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
            default => null !== $this->blueprintRegistry->getMappingBlueprint($className),
        };
    }

    private static function isObjectOrIterableOrMixed(): TypeSpecification
    {
        /** @phpstan-var ?AnyType $mixedTypesSpecification */
        static $mixedTypesSpecification;

        return $mixedTypesSpecification ??= new AnyType([
            PropertyExceptionMappingBlueprint::isObject(),
            PropertyExceptionMappingBlueprint::isIterable(),
            new MixedType(),
        ]);
    }
}
