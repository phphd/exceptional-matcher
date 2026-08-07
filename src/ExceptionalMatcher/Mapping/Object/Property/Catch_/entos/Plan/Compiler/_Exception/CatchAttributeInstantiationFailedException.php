<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\entos\Plan\Compiler\_Exception;

use RuntimeException;
use Throwable;

final class CatchAttributeInstantiationFailedException extends RuntimeException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('#[Catch_] attribute instantiation has failed.', previous: $previous);
    }
}
