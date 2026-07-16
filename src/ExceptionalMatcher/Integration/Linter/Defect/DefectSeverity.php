<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Defect;

/** @api */
enum DefectSeverity: string
{
    case Error = 'error';
    case Warning = 'warning';
}
