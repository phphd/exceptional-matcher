<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use Throwable;

use function array_reverse;
use function implode;

/** @internal */
final class MappingDefect
{
    private function __construct(
        private readonly DefectSeverity $severity,
        /** @var non-empty-list<string> */
        private readonly array $messages,
        private readonly DefectLocation $location,
        private readonly ?Throwable $cause,
    ) {
    }

    public static function error(DefectLocation $location, Throwable $cause): self
    {
        return new self(DefectSeverity::Error, self::unwind($cause), $location, $cause);
    }

    public static function warning(string $message, DefectLocation $location): self
    {
        return new self(DefectSeverity::Warning, [$message], $location, null);
    }

    public static function notice(string $message, DefectLocation $location): self
    {
        return new self(DefectSeverity::Notice, [$message], $location, null);
    }

    public function getSeverity(): DefectSeverity
    {
        return $this->severity;
    }

    /**
     * What went wrong, followed by the failures it in turn caused.
     *
     * @return non-empty-list<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMessage(): string
    {
        return implode("\n", $this->messages);
    }

    public function getLocation(): DefectLocation
    {
        return $this->location;
    }

    public function getCause(): ?Throwable
    {
        return $this->cause;
    }

    /**
     * Exceptions nest from the outermost failure down to its root cause, and are reported the other way round.
     *
     * @return non-empty-list<string>
     */
    private static function unwind(Throwable $cause): array
    {
        $messages = [$cause->getMessage()];

        for ($previous = $cause->getPrevious(); null !== $previous; $previous = $previous->getPrevious()) {
            $messages[] = $previous->getMessage();
        }

        return array_reverse($messages);
    }
}
