<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location;

use ReflectionProperty;

/** @internal */
final class DefectLocation
{
    public function __construct(
        /** @var class-string */
        private readonly string $className,
        private readonly ?string $propertyName = null,
    ) {
    }

    public static function ofProperty(ReflectionProperty $property): self
    {
        return new self(
            $property->getDeclaringClass()
                ->getName(),
            $property->getName(),
        );
    }

    /** @return class-string */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }
}
