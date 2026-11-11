<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Type;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\MixedType;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\TypeSpecification;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_any;
use function sprintf;

final class PropertyType
{
    public function __construct(
        private readonly ?ReflectionType $type,
    ) {
    }

    public function assertIncludes(TypeSpecification $specification, string $message): void
    {
        if (!$this->includes($specification)) {
            throw new LogicException(sprintf($message, (string)$this->type ?: 'mixed')); // @phpstan-ignore ternary.shortNotAllowed
        }
    }

    public function includes(TypeSpecification $specification): bool
    {
        return $this->namedTypeCovers($specification)
            ?? $this->compoundTypeIncludes($specification)
            ?? $this->isMixed();
    }

    private function namedTypeCovers(TypeSpecification $specification): ?bool
    {
        if (!$this->type instanceof ReflectionNamedType) {
            return null;
        }

        return $specification->isSatisfiedBy($this->type);
    }

    private function compoundTypeIncludes(TypeSpecification $specification): ?bool
    {
        if (!$this->type instanceof ReflectionUnionType && !$this->type instanceof ReflectionIntersectionType) {
            return null;
        }

        return array_any(
            $this->type->getTypes(),
            fn(ReflectionType $reflectionType): bool => (new self($reflectionType))->includes($specification),
        );
    }

    private function isMixed(): bool
    {
        return (new MixedType())->isSatisfiedBy($this->type);
    }
}
