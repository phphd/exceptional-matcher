<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\Exception;

use RuntimeException;
use Throwable;

final class CatchPlanCompilationFailedException extends RuntimeException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('#[Catch_] attribute compilation has failed.', previous: $previous);
    }
}
