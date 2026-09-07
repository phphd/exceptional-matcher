<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity;

/** @internal */
enum DefectSeverity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Notice = 'notice';

    public function is(self $other): bool
    {
        return $this === $other;
    }
}
