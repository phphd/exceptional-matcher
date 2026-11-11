<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests\Unit\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\AnException;

final class MessageWithNoTryAttribute
{
    private ?int $noCatchMapping = null;

    public function __construct(
        #[Catch_(AnException::class, message: 'not matched')]
        private readonly int $property,
    ) {
    }
}
