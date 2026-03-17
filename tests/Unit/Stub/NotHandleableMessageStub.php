<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\AnException;

final class NotHandleableMessageStub
{
    public function __construct(
        #[Catch_(AnException::class, 'not matched')]
        private readonly int $property,
    ) {
    }
}
