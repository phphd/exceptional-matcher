<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Defect;

/** @api */
final class DefectLocation
{
    public function __construct(
        /** @var class-string */
        private readonly string $className,
        private readonly ?string $propertyName = null,
    ) {
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
