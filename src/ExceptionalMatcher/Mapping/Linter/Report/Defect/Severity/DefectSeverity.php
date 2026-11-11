<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity;

/** @internal */
enum DefectSeverity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Notice = 'notice';

    public function trans(int $count): string
    {
        if (1 === $count) {
            return $this->value;
        }

        return $this->value.'s';
    }

    public function is(self $other): bool
    {
        return $this === $other;
    }
}
