<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

/** @internal */
final class MixedObjectType implements TypeSpecification
{
    private const TYPE_OBJECT = 'object';

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        return $type->isBuiltin() && self::TYPE_OBJECT === $type->getName();
    }
}
