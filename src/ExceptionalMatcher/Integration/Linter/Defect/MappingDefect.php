<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Defect;

use Throwable;

/** @api */
final class MappingDefect
{
    private function __construct(
        private readonly DefectSeverity $severity,
        private readonly string $message,
        private readonly DefectLocation $location,
        private readonly ?Throwable $cause,
    ) {
    }

    public static function error(DefectLocation $location, Throwable $cause): self
    {
        return new self(DefectSeverity::Error, $cause->getMessage(), $location, $cause);
    }

    public static function warning(string $message, DefectLocation $location): self
    {
        return new self(DefectSeverity::Warning, $message, $location, null);
    }

    public function getSeverity(): DefectSeverity
    {
        return $this->severity;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLocation(): DefectLocation
    {
        return $this->location;
    }

    public function getCause(): ?Throwable
    {
        return $this->cause;
    }
}
