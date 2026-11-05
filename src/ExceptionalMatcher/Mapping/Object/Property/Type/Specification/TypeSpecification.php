<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

/** @internal */
interface TypeSpecification
{
    public function __invoke(mixed $value): bool;

    public function isSatisfiedBy(ReflectionNamedType $type): bool;
}
