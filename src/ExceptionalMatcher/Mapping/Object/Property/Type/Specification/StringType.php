<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

/** @internal */
final class StringType implements TypeSpecification
{
    private const TYPE_STRING = 'string';

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        return $type->isBuiltin() && self::TYPE_STRING === $type->getName();
    }
}
