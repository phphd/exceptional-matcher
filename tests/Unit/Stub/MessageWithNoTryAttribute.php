<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\AnException;

final class MessageWithNoTryAttribute
{
    public function __construct(
        #[Catch_(AnException::class, message: 'not matched')]
        private readonly int $property,
    ) {
    }
}
