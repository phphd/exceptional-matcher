<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Symfony\Uid\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use const PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Symfony\Uid\uid_value;

#[Try_]
final class MessageWithInvalidUidCondition
{
    public function __construct(
        #[Catch_(InvalidUidException::class, match: uid_value)]
        public int|string|null $id,
    ) {
    }
}
