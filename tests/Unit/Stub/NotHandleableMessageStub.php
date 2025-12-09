<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;

final readonly class NotHandleableMessageStub
{
    public function __construct(
        #[Capture(PropertyCapturableException::class, 'not captured')]
        private int $property,
    ) {
    }
}
