<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;

final class NotHandleableMessageStub
{
    public function __construct(
        #[Catch_(PropertyCapturableException::class, 'not captured')]
        private readonly int $property,
    ) {
    }
}
