<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Tests\Stub\Message;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Throwable;

/** @implements ExceptionMatchFormatter<Throwable,string> */
final class ExceptionMessageFormatter implements ExceptionMatchFormatter
{
    /** @return array{string} */
    public function format(ExceptionMatch $exceptionMatch): array
    {
        return [$exceptionMatch->getException()->getMessage()];
    }
}
