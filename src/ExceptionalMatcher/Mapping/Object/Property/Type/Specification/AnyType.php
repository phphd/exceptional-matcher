<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

use function array_any;

/** @internal */
final class AnyType implements TypeSpecification
{
    public function __construct(
        /** @var list<TypeSpecification> */
        private readonly array $specifications,
    ) {
    }

    public function __invoke(mixed $value): bool
    {
        return array_any(
            $this->specifications,
            static fn (TypeSpecification $specification): bool => $specification->__invoke($value),
        );
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        return array_any(
            $this->specifications,
            static fn (TypeSpecification $specification): bool => $specification->isSatisfiedBy($type),
        );
    }
}
