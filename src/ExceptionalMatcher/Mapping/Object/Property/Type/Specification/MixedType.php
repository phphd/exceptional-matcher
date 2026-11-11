<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;
use ReflectionType;

/** @internal */
final class MixedType implements TypeSpecification
{
    private const TYPE_MIXED = 'mixed';

    public function __invoke(mixed $value): bool
    {
        return true;
    }

    public function isSatisfiedBy(?ReflectionType $type): bool
    {
        if (null === $type) {
            return true; // untyped property
        }

        if (!$type instanceof ReflectionNamedType) {
            return false;
        }

        return self::TYPE_MIXED === $type->getName();
    }
}
